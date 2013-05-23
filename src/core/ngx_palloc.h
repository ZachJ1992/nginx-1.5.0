
/*
 * Copyright (C) Igor Sysoev
 * Copyright (C) Nginx, Inc.
 */
// 参考文章 http://blog.csdn.net/v_july_v/article/details/7040425

#ifndef _NGX_PALLOC_H_INCLUDED_
#define _NGX_PALLOC_H_INCLUDED_


#include <ngx_config.h>
#include <ngx_core.h>


/*
 * NGX_MAX_ALLOC_FROM_POOL should be (ngx_pagesize - 1), i.e. 4095 on x86.
 * On Windows NT it decreases a number of locked pages in a kernel.
 */
#define NGX_MAX_ALLOC_FROM_POOL  (ngx_pagesize - 1)   // x86上面一般是4095，即4K - 1

#define NGX_DEFAULT_POOL_SIZE    (16 * 1024)          // 16K

#define NGX_POOL_ALIGNMENT       16                   // 内存池对齐位数
#define NGX_MIN_POOL_SIZE                                                     \
    ngx_align((sizeof(ngx_pool_t) + 2 * sizeof(ngx_pool_large_t)),            \
              NGX_POOL_ALIGNMENT)                     // 内存池实际尺寸


typedef void (*ngx_pool_cleanup_pt)(void *data);

typedef struct ngx_pool_cleanup_s  ngx_pool_cleanup_t;

struct ngx_pool_cleanup_s {
    ngx_pool_cleanup_pt   handler;  // 内存池清理handler
    void                 *data;     // 数据
    ngx_pool_cleanup_t   *next;     //
};


typedef struct ngx_pool_large_s  ngx_pool_large_t;

struct ngx_pool_large_s { // 大块数据分配的结构体
    ngx_pool_large_t     *next;
    void                 *alloc;
};


typedef struct {  // 内存池的数据结构模块
    u_char               *last;   // 当前内存分配的结束位置
    u_char               *end;    // 内存池的结束位置
    ngx_pool_t           *next;   // 链接到下一个内存池，内存池的很多块内存就是通过该指针连成链表的 
    ngx_uint_t            failed; // 记录内存分配不能满足需求的失败次数
} ngx_pool_data_t;  // 结构用来维护内存池的数据块，供用户分配之用。


struct ngx_pool_s {  //内存池的管理分配模块
    ngx_pool_data_t       d;  // 内存池的数据块
    size_t                max; // 数据块大小，小块内存的最大值
    ngx_pool_t           *current; // 指向当前或本内存池
    ngx_chain_t          *chain;  // 该指针挂接一个ngx_chain_t结构
    ngx_pool_large_t     *large; // 指向大块内存分配，nginx中，大块内存分配直接采用标准系统接口malloc
    ngx_pool_cleanup_t   *cleanup; // 析构函数，挂载内存释放时需要清理资源的一些必要操作
    ngx_log_t            *log; // 内存分配相关的日志记录
};


typedef struct {
    ngx_fd_t              fd;           // nginx文件描述符
    u_char               *name;         // 名称
    ngx_log_t            *log;          // 日志
} ngx_pool_cleanup_file_t;


void *ngx_alloc(size_t size, ngx_log_t *log);
void *ngx_calloc(size_t size, ngx_log_t *log);

ngx_pool_t *ngx_create_pool(size_t size, ngx_log_t *log);
void ngx_destroy_pool(ngx_pool_t *pool);
void ngx_reset_pool(ngx_pool_t *pool);

void *ngx_palloc(ngx_pool_t *pool, size_t size);
void *ngx_pnalloc(ngx_pool_t *pool, size_t size);
void *ngx_pcalloc(ngx_pool_t *pool, size_t size);
void *ngx_pmemalign(ngx_pool_t *pool, size_t size, size_t alignment);
ngx_int_t ngx_pfree(ngx_pool_t *pool, void *p);


ngx_pool_cleanup_t *ngx_pool_cleanup_add(ngx_pool_t *p, size_t size);
void ngx_pool_run_cleanup_file(ngx_pool_t *p, ngx_fd_t fd);
void ngx_pool_cleanup_file(void *data);
void ngx_pool_delete_file(void *data);


#endif /* _NGX_PALLOC_H_INCLUDED_ */
