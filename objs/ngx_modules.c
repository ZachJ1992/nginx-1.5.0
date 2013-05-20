
#include <ngx_config.h>
#include <ngx_core.h>


extern ngx_module_t  ngx_core_module;
extern ngx_module_t  ngx_errlog_module;
extern ngx_module_t  ngx_conf_module;
extern ngx_module_t  ngx_events_module;
extern ngx_module_t  ngx_event_core_module;
extern ngx_module_t  ngx_epoll_module;
extern ngx_module_t  ngx_regex_module;
extern ngx_module_t  ngx_http_module;
extern ngx_module_t  ngx_http_core_module;
extern ngx_module_t  ngx_http_log_module;
extern ngx_module_t  ngx_http_upstream_module;
extern ngx_module_t  ngx_http_static_module;
extern ngx_module_t  ngx_http_autoindex_module;
extern ngx_module_t  ngx_http_index_module;
extern ngx_module_t  ngx_http_auth_basic_module;
extern ngx_module_t  ngx_http_access_module;
extern ngx_module_t  ngx_http_limit_conn_module;
extern ngx_module_t  ngx_http_limit_req_module;
extern ngx_module_t  ngx_http_geo_module;
extern ngx_module_t  ngx_http_map_module;
extern ngx_module_t  ngx_http_split_clients_module;
extern ngx_module_t  ngx_http_referer_module;
extern ngx_module_t  ngx_http_rewrite_module;
extern ngx_module_t  ngx_http_proxy_module;
extern ngx_module_t  ngx_http_fastcgi_module;
extern ngx_module_t  ngx_http_uwsgi_module;
extern ngx_module_t  ngx_http_scgi_module;
extern ngx_module_t  ngx_http_memcached_module;
extern ngx_module_t  ngx_http_empty_gif_module;
extern ngx_module_t  ngx_http_browser_module;
extern ngx_module_t  ngx_http_upstream_ip_hash_module;
extern ngx_module_t  ngx_http_upstream_least_conn_module;
extern ngx_module_t  ngx_http_upstream_keepalive_module;
extern ngx_module_t  ngx_http_write_filter_module;
extern ngx_module_t  ngx_http_header_filter_module;
extern ngx_module_t  ngx_http_chunked_filter_module;
extern ngx_module_t  ngx_http_range_header_filter_module;
extern ngx_module_t  ngx_http_gzip_filter_module;
extern ngx_module_t  ngx_http_postpone_filter_module;
extern ngx_module_t  ngx_http_ssi_filter_module;
extern ngx_module_t  ngx_http_charset_filter_module;
extern ngx_module_t  ngx_http_userid_filter_module;
extern ngx_module_t  ngx_http_headers_filter_module;
extern ngx_module_t  ngx_http_copy_filter_module;
extern ngx_module_t  ngx_http_range_body_filter_module;
extern ngx_module_t  ngx_http_not_modified_filter_module;
// 这个文件是编译后生成的文件， nginx共有44个模块
//ngx_modules数组结构如下:
//+-----+  ------------------------------------------------+
//|  0  |                  ngx_core_module                 |
//+-----+  ------------------------------------------------+
//|  1  |                 ngx_errlog_module                |
//+-----+  ------------------------------------------------+
//|  2  |                 ngx_conf_module                  |
//+-----+  ------------------------------------------------+
//|  3  |                 ngx_events_module                |
//+-----+  ------------------------------------------------+
//|  4  |               ngx_event_core_module              |
//+-----+  ------------------------------------------------+
//|  5  |                 ngx_epoll_module                 |
//+-----+  ------------------------------------------------+
//|  6  |                 ngx_regex_module                 |
//+-----+  ------------------------------------------------+
//|  7  |                 ngx_http_module                  |
//+-----+  ------------------------------------------------+
//|  8  |                 ngx_http_core_module             |
//+-----+  ------------------------------------------------+
//|  9  |                 ngx_http_log_module              |
//+-----+  ------------------------------------------------+
//| ... |                                                  |
//+-----+  ------------------------------------------------+
//|  41 |       ngx_http_copy_filter_module                |
//+-----+  ------------------------------------------------+
//|  42 |    ngx_http_range_body_filter_module             |
//+-----+  ------------------------------------------------+
//|  43 |      ngx_http_not_modified_filter_module         |
//+-----+  ------------------------------------------------+
//|  44 |                     NULL                         |
//+-----+  ------------------------------------------------+
// 在对全局数组ngx_modules进行初始化时，即对每一个模块进行了静态初始化。
// 其中对模块的type字段的初始化是通过以下4个宏进行的。
// 1. ./src/core/ngx_conf_file.h中定义的CORE和CONF.
// #define NGX_CORE_MODULE      0x45524F43   /* "CORE" */
// #define NGX_CONF_MODULE      0x464E4F43   /* "CONF" */
// 2. ./src/event/ngx_event.h中定义的EVENT
// #define NGX_EVENT_MODULE      0x544E5645  /* "EVNT" */
// 3. ./src/http/ngx_http_config.h中定义的HTTP
// #define NGX_HTTP_MODULE       0x50545448  /* "HTTP" */
// 即模块种类宏，定义为一个十六进制的数，这个十六进制的数就是其类型对应的ASCII码。
// 因此，nginx共有4种类型的模块，分别为"CORE","CONF","EVNT","HTTP"。
// 实际上，如果在configure阶段，使用了"--with-mail"参数，mail模块将被编译进来，其对应的宏如下。
// #define NGX_MAIL_MODULE       0x4C49414D  /* "MAIL" */
// 因此，严格来讲，nginx有5中类型的模块，"CORE","CONF","EVNT","HTTP","MAIL"。

ngx_module_t *ngx_modules[] = {
    &ngx_core_module,
    &ngx_errlog_module,
    &ngx_conf_module,
    &ngx_events_module,
    &ngx_event_core_module,
    &ngx_epoll_module,
    &ngx_regex_module,
    &ngx_http_module,
    &ngx_http_core_module,
    &ngx_http_log_module,
    &ngx_http_upstream_module,
    &ngx_http_static_module,
    &ngx_http_autoindex_module,
    &ngx_http_index_module,
    &ngx_http_auth_basic_module,
    &ngx_http_access_module,
    &ngx_http_limit_conn_module,
    &ngx_http_limit_req_module,
    &ngx_http_geo_module,
    &ngx_http_map_module,
    &ngx_http_split_clients_module,
    &ngx_http_referer_module,
    &ngx_http_rewrite_module,
    &ngx_http_proxy_module,
    &ngx_http_fastcgi_module,
    &ngx_http_uwsgi_module,
    &ngx_http_scgi_module,
    &ngx_http_memcached_module,
    &ngx_http_empty_gif_module,
    &ngx_http_browser_module,
    &ngx_http_upstream_ip_hash_module,
    &ngx_http_upstream_least_conn_module,
    &ngx_http_upstream_keepalive_module,
    &ngx_http_write_filter_module,
    &ngx_http_header_filter_module,
    &ngx_http_chunked_filter_module,
    &ngx_http_range_header_filter_module,
    &ngx_http_gzip_filter_module,
    &ngx_http_postpone_filter_module,
    &ngx_http_ssi_filter_module,
    &ngx_http_charset_filter_module,
    &ngx_http_userid_filter_module,
    &ngx_http_headers_filter_module,
    &ngx_http_copy_filter_module,
    &ngx_http_range_body_filter_module,
    &ngx_http_not_modified_filter_module,
    NULL
};

