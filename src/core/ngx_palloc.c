
/*
 * Copyright (C) Igor Sysoev
 * Copyright (C) Nginx, Inc.
 */


#include <ngx_config.h>
#include <ngx_core.h>


static void *ngx_palloc_block(ngx_pool_t *pool, size_t size);
static void *ngx_palloc_large(ngx_pool_t *pool, size_t size);


ngx_pool_t *
ngx_create_pool(size_t size, ngx_log_t *log)
{
    ngx_pool_t  *p;
    //ngx_memalign()函数执行内存分配，该函数的实现在./src/os/unix/ngx_alloc.c文件中（假定NGX_HAVE_POSIX_MEMALIGN被定义）
    p = ngx_memalign(NGX_POOL_ALIGNMENT, size, log);
    if (p == NULL) {
        return NULL;
    }

    p->d.last = (u_char *) p + sizeof(ngx_pool_t);
    p->d.end = (u_char *) p + size;
    p->d.next = NULL;
    p->d.failed = 0;

    size = size - sizeof(ngx_pool_t);
    // 最大不超过4095B，别忘了上面NGX_MAX_ALLOC_FROM_POOL的定义
    p->max = (size < NGX_MAX_ALLOC_FROM_POOL) ? size : NGX_MAX_ALLOC_FROM_POOL;

    p->current = p;
    p->chain = NULL;
    p->large = NULL;
    p->cleanup = NULL;
    p->log = log;

    return p;
}

//该函数将遍历内存池链表，所有释放内存，如果注册了clenup(也是一个链表结构)，亦将遍历该cleanup链表结构依次调用clenup的handler清理。同时，还将遍历large链表，释放大块内存。
void
ngx_destroy_pool(ngx_pool_t *pool)
{
    ngx_pool_t          *p, *n;
    ngx_pool_large_t    *l;
    ngx_pool_cleanup_t  *c;
    //cleanup指向析构函数，用于执行相关的内存池销毁之前的清理工作，如文件的关闭等，
    //清理函数是一个handler的函数指针挂载。因此，在这部分，对内存池中的析构函数遍历调用。
    for (c = pool->cleanup; c; c = c->next) {
        if (c->handler) {
            ngx_log_debug1(NGX_LOG_DEBUG_ALLOC, pool->log, 0,
                           "run cleanup: %p", c);
            c->handler(c->data);
        }
    }

    //这一部分用于清理大块内存，ngx_free实际上就是标准的free函数，  
    //即大内存块就是通过malloc和free操作进行管理的。  
    for (l = pool->large; l; l = l->next) {

        ngx_log_debug1(NGX_LOG_DEBUG_ALLOC, pool->log, 0, "free: %p", l->alloc);

        if (l->alloc) {
            ngx_free(l->alloc);
        }
    }

#if (NGX_DEBUG)

    /*
     * we could allocate the pool->log from this pool
     * so we cannot use this log while free()ing the pool
     */
    //只有debug模式才会执行这个片段的代码，主要是log记录，用以跟踪函数销毁时日志记录。
    for (p = pool, n = pool->d.next; /* void */; p = n, n = n->d.next) {
        ngx_log_debug2(NGX_LOG_DEBUG_ALLOC, pool->log, 0,
                       "free: %p, unused: %uz", p, p->d.end - p->d.last);

        if (n == NULL) {
            break;
        }
    }

#endif
    //该片段彻底销毁内存池本身.
    for (p = pool, n = pool->d.next; /* void */; p = n, n = n->d.next) {
        ngx_free(p);

        if (n == NULL) {
            break;
        }
    }
}

//重置内存池，将内存池恢复到刚分配时的初始化状态，注意内存池分配的初始状态时，是不包含大块内存的，
//因此初始状态需要将使用的大块内存释放掉，并把内存池数据结构的各项指针恢复到初始状态值。
//这里虽然重置了内存池，但可以看到并没有释放内存池中被使用的小块内存，而只是将其last指针指向可共分配的内存的初始位置。
//这样，就省去了内存池的释放和重新分配操作，而达到重置内存池的目的。
void
ngx_reset_pool(ngx_pool_t *pool)
{
    ngx_pool_t        *p;
    ngx_pool_large_t  *l;
    // 下面代码段主要用于清理使用到的大块内存。
    for (l = pool->large; l; l = l->next) {
        if (l->alloc) {
            ngx_free(l->alloc);
        }
    }

    pool->large = NULL;

    for (p = pool; p; p = p->d.next) {
        p->d.last = (u_char *) p + sizeof(ngx_pool_t);
    }
}

//ngx_palloc从pool内存池分配以NGX_ALIGNMENT对齐的内存
void *
ngx_palloc(ngx_pool_t *pool, size_t size)
{
    u_char      *m;
    ngx_pool_t  *p;
    //判断待分配内存与max值  
    //1、小于max值，则从current结点开始遍历pool链表  
    if (size <= pool->max) {

        p = pool->current;

        do {
            //执行对齐操作，
            //即以last开始，计算以NGX_ALIGNMENT对齐的偏移位置指针，
            m = ngx_align_ptr(p->d.last, NGX_ALIGNMENT);
            //然后计算end值减去这个偏移指针位置的大小是否满足索要分配的size大小，
            //如果满足，则移动last指针位置，并返回所分配到的内存地址的起始地址；
            if ((size_t) (p->d.end - m) >= size) {
                p->d.last = m + size;
                //在该结点指向的内存块中分配size大小的内存  
                return m;
            }
            //如果不满足，则查找下一个链。
            p = p->d.next;

        } while (p);
        //如果遍历完整个内存池链表均未找到合适大小的内存块供分配，则执行ngx_palloc_block()来分配。

        //ngx_palloc_block()函数为该内存池再分配一个block，该block的大小为链表中前面每一个block大小的值。
        //一个内存池是由多个block链接起来的。分配成功后，将该block链入该poll链的最后，
        //同时，为所要分配的size大小的内存进行分配，并返回分配内存的起始地址。
        return ngx_palloc_block(pool, size);
    }
    //2、如果大于max值，则执行大块内存分配的函数ngx_palloc_large，在large链表里分配内存
    return ngx_palloc_large(pool, size);
}

//ngx_pnalloc分配适合size大小的内存，不考虑内存对齐。
void *
ngx_pnalloc(ngx_pool_t *pool, size_t size)
{
    u_char      *m;
    ngx_pool_t  *p;

    if (size <= pool->max) {

        p = pool->current;

        do {
            m = p->d.last;

            if ((size_t) (p->d.end - m) >= size) {
                p->d.last = m + size;

                return m;
            }

            p = p->d.next;

        } while (p);

        return ngx_palloc_block(pool, size);
    }

    return ngx_palloc_large(pool, size);
}

// 注意：该函数分配一块内存后，last指针指向的是ngx_pool_data_t结构体(大小16B)之后数据区的起始位置，
//       而创建内存池时时，last指针指向的是ngx_pool_t结构体(大小40B)之后数据区的起始位置。
static void *
ngx_palloc_block(ngx_pool_t *pool, size_t size)
{
    u_char      *m;
    size_t       psize;
    ngx_pool_t  *p, *new, *current;
    //计算pool的大小，即需要分配的block的大小
    psize = (size_t) (pool->d.end - (u_char *) pool);
    //执行按NGX_POOL_ALIGNMENT对齐方式的内存分配，假设能够分配成功，则继续执行后续代码片段。
    m = ngx_memalign(NGX_POOL_ALIGNMENT, psize, pool->log);
    if (m == NULL) {
        return NULL;
    }
    //这里计算需要分配的block的大小
    new = (ngx_pool_t *) m;
    //执行该block相关的初始化。
    new->d.end = m + psize;
    new->d.next = NULL;
    new->d.failed = 0;
    //让m指向该块内存ngx_pool_data_t结构体之后数据区起始位置
    m += sizeof(ngx_pool_data_t);
    m = ngx_align_ptr(m, NGX_ALIGNMENT);
    //在数据区分配size大小的内存并设置last指针
    new->d.last = m + size;

    current = pool->current;

    for (p = current; p->d.next; p = p->d.next) {
        if (p->d.failed++ > 4) {//失败4次以上移动current指针
            current = p->d.next;
        }
    }
    //将分配的block链入内存池
    p->d.next = new;
    //如果是第一次为内存池分配block，这current将指向新分配的block。
    pool->current = current ? current : new;

    return m;
}

// 待分配内存大于max值的情况
// 这是一个static的函数，说明外部函数不会随便调用，而是提供给内部分配调用的，
// 即nginx在进行内存分配需求时，不会自行去判断是否是大块内存还是小块内存，
// 而是交由内存分配函数去判断，对于用户需求来说是完全透明的。
static void *
ngx_palloc_large(ngx_pool_t *pool, size_t size)
{
    void              *p;
    ngx_uint_t         n;
    ngx_pool_large_t  *large;

    p = ngx_alloc(size, pool->log);
    if (p == NULL) {
        return NULL;
    }

    n = 0;

    //以下几行，将分配的内存链入pool的large链中，
    //这里指原始pool在之前已经分配过large内存的情况。
    for (large = pool->large; large; large = large->next) {
        if (large->alloc == NULL) {
            large->alloc = p;
            return p;
        }

        if (n++ > 3) {
            break;
        }
    }
    //如果该pool之前并未分配large内存，则就没有ngx_pool_large_t来管理大块内存
    //执行ngx_pool_large_t结构体的分配，用于来管理large内存块。
    large = ngx_palloc(pool, sizeof(ngx_pool_large_t));
    if (large == NULL) {
        ngx_free(p);
        return NULL;
    }

    large->alloc = p;
    large->next = pool->large;
    pool->large = large;

    return p;
}

//ngx_pmemalign将在分配size大小的内存并按alignment对齐，然后挂到large字段下，当做大块内存处理。
void *
ngx_pmemalign(ngx_pool_t *pool, size_t size, size_t alignment)
{
    void              *p;
    ngx_pool_large_t  *large;

    p = ngx_memalign(alignment, size, pool->log);
    if (p == NULL) {
        return NULL;
    }

    large = ngx_palloc(pool, sizeof(ngx_pool_large_t));
    if (large == NULL) {
        ngx_free(p);
        return NULL;
    }

    large->alloc = p;
    large->next = pool->large;
    pool->large = large;

    return p;
}

//需要注意的是该函数只释放large链表中注册的内存，普通内存在ngx_destroy_pool中统一释放。
ngx_int_t
ngx_pfree(ngx_pool_t *pool, void *p)
{
    ngx_pool_large_t  *l;

    for (l = pool->large; l; l = l->next) {
        if (p == l->alloc) {
            ngx_log_debug1(NGX_LOG_DEBUG_ALLOC, pool->log, 0,
                           "free: %p", l->alloc);
            ngx_free(l->alloc);
            l->alloc = NULL;

            return NGX_OK;
        }
    }

    return NGX_DECLINED;
}

//ngx_pcalloc是直接调用palloc分配好内存，然后进行一次0初始化操作
void *
ngx_pcalloc(ngx_pool_t *pool, size_t size)
{
    void *p;

    p = ngx_palloc(pool, size);
    if (p) {
        ngx_memzero(p, size);
    }

    return p;
}

//注册cleanup
ngx_pool_cleanup_t *
ngx_pool_cleanup_add(ngx_pool_t *p, size_t size)
{
    ngx_pool_cleanup_t  *c;

    c = ngx_palloc(p, sizeof(ngx_pool_cleanup_t));
    if (c == NULL) {
        return NULL;
    }

    if (size) {
        c->data = ngx_palloc(p, size);
        if (c->data == NULL) {
            return NULL;
        }

    } else {
        c->data = NULL;
    }

    c->handler = NULL;
    c->next = p->cleanup;

    p->cleanup = c;

    ngx_log_debug1(NGX_LOG_DEBUG_ALLOC, p->log, 0, "add cleanup: %p", c);

    return c;
}

//一些文件相关的操作函数
void
ngx_pool_run_cleanup_file(ngx_pool_t *p, ngx_fd_t fd)
{
    ngx_pool_cleanup_t       *c;
    ngx_pool_cleanup_file_t  *cf;

    for (c = p->cleanup; c; c = c->next) {
        if (c->handler == ngx_pool_cleanup_file) {

            cf = c->data;

            if (cf->fd == fd) {
                c->handler(cf);
                c->handler = NULL;
                return;
            }
        }
    }
}


void
ngx_pool_cleanup_file(void *data)
{
    ngx_pool_cleanup_file_t  *c = data;

    ngx_log_debug1(NGX_LOG_DEBUG_ALLOC, c->log, 0, "file cleanup: fd:%d",
                   c->fd);

    if (ngx_close_file(c->fd) == NGX_FILE_ERROR) {
        ngx_log_error(NGX_LOG_ALERT, c->log, ngx_errno,
                      ngx_close_file_n " \"%s\" failed", c->name);
    }
}


void
ngx_pool_delete_file(void *data)
{
    ngx_pool_cleanup_file_t  *c = data;

    ngx_err_t  err;

    ngx_log_debug2(NGX_LOG_DEBUG_ALLOC, c->log, 0, "file cleanup: fd:%d %s",
                   c->fd, c->name);

    if (ngx_delete_file(c->name) == NGX_FILE_ERROR) {
        err = ngx_errno;

        if (err != NGX_ENOENT) {
            ngx_log_error(NGX_LOG_CRIT, c->log, err,
                          ngx_delete_file_n " \"%s\" failed", c->name);
        }
    }

    if (ngx_close_file(c->fd) == NGX_FILE_ERROR) {
        ngx_log_error(NGX_LOG_ALERT, c->log, ngx_errno,
                      ngx_close_file_n " \"%s\" failed", c->name);
    }
}


#if 0

static void *
ngx_get_cached_block(size_t size)
{
    void                     *p;
    ngx_cached_block_slot_t  *slot;

    if (ngx_cycle->cache == NULL) {
        return NULL;
    }

    slot = &ngx_cycle->cache[(size + ngx_pagesize - 1) / ngx_pagesize];

    slot->tries++;

    if (slot->number) {
        p = slot->block;
        slot->block = slot->block->next;
        slot->number--;
        return p;
    }

    return NULL;
}

#endif
