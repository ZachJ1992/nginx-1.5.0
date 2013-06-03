#!/bin/bash
#监控系统负载与cpu、内存、硬盘、登录用户数

#提取本服务器的IP地址信息
IP=`ifconfig eth0 | grep "inet addr" | cut -f 2 -d ":" | cut -f 1 -d " "` 
#cpu的总核数
cpu_num=`grep -c 'model name' /proc/cpuinfo`
 
count_uptime=`uptime |wc -w`
#当前系统15分钟的平均负载值
load_15=`uptime | awk '{print $'$count_uptime'}'`
 
#计算机当前系统单个核心15分钟的平均负载值，结果小于1.0时前面个位数补0
average_load=`echo "scale=2;a=$load_15/$cpu_num;if(length(a)==scale(a)) print 0;print a" | bc`  
#取上面平均负载值的个位整数
average_int=`echo $average_load | cut -f 1 -d "."`  
#设置警告值
load_warn=0.70  
 
if [ $average_int -gt 0 ]
then
echo "$IP服务器单个核心15分钟的平均负载为$average_load，超过警戒值1.0，请立即处理！！！$(date +%Y%m%d/%H:%M:%S)" >>/usr/monitor/performance/performance_$(date +%Y%m%d).log
        echo "$IP服务器单个核心15分钟的平均负载为$average_load，超过警戒值1.0，请立即处理！！！$(date +%Y%m%d/%H:%M:%S)" | mail -s "$IP服务器系统负载严重告警" denghj@belrare.com
else
 
load_now=`expr $average_load \> $load_warn`
 
if [ $load_now -eq 1 ]
then 
echo "$IP服务器单个核心15分钟的平均负载为$average_load，超过警戒值0.7，请立即处理！！！$(date +%Y%m%d/%H:%M:%S)">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
echo "$IP服务器单个核心15分钟的平均负载为$average_load，超过警戒值0.7，请立即处理！！！$(date +%Y%m%d/%H:%M:%S)" | mail -s "$IP服务器系统负载告警" denghj@belrare.com
else
echo "$IP服务器单个核心15分钟的平均负载值为$average_load,cpu核心数为$cpu_num,系统15分钟的平均负载为$load_15 负载正常   $(date +%Y%m%d/%H:%M:%S)">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
fi
 
fi
 
 
#cpu使用率
cpu_idle=`top -b -n 1 | grep Cpu | awk '{print $5}' | cut -f 1 -d "."`  
if [ $cpu_idle -lt 20 ]
then
echo "$IP服务器cpu剩余$cpu_idle%,使用率已经超过80%,请及时处理。">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
echo "$IP服务器cpu剩余$cpu_idle%,使用率已经超过80%,请及时处理！！！" | mail -s "$IP服务器cpu告警" denghj@belrare.com
else
echo "$IP服务器cpu剩余$cpu_idle%,使用率正常">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
fi
 
 
 
 
#系统分配的交换分区总量
swap_total=`free -m | grep Swap | awk '{print  $2}'`
 
#当前剩余的交换分区free大小
swap_free=`free -m | grep Swap | awk '{print  $4}'`
 
#当前已使用的交换分区used大小
swap_used=`free -m | grep Swap | awk '{print  $3}'`
 
if [ $swap_used -ne 0 ]
then
 #如果交换分区已被使用，则计算当前剩余交换分区free所占总量的百分比，用小数来表示，要在小数点前面补一个整数位0
swap_per=0`echo "scale=2;$swap_free/$swap_total" | bc`
#设置交换分区的告警值为20%(即使用超过80%的时候告警)。
swap_warn=0.20
#当前剩余交换分区百分比与告警值进行比较（当大于告警值(即剩余20%以上)时会返回1，小于(即剩余不足20%)时会返回0 ）
swap_now=`expr $swap_per \> $swap_warn`
#如果当前交换分区使用超过80%（即剩余小于20%，上面的返回值等于0），立即发邮件告警
if [ $swap_now -eq 0 ]
then
        echo "$IP服务器swap交换分区只剩下 $swap_free M 未使用，剩余不足20%，使用率已经超过80%，请及时处理。">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
echo "$IP服务器swap交换分区只剩下 $swap_free M 未使用，剩余不足20%, 使用率已经超过80%, 请及时处理。" | mail -s "$IP服务器内存告警" denghj@belrare.com
 
else
        echo "$IP服务器swap交换分区剩下 $swap_free M未使用，使用率正常">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
  fi
 
else
        echo "$IP服务器交换分区未使用"  >>/usr/monitor/performance/performance_$(date +%Y%m%d).log
fi
 
 
 
 
 
 
#取当前根分区（/dev/sda3）已用的百份比值（只取整数部分）
disk_sda1=`df -h | grep /dev/sda1 | awk '{print $5}' | cut -f 1 -d "%"`
#设置空闲硬盘容量的告警值为80%，如果当前硬盘使用超过80%，立即发邮件告警
 
if [ $disk_sda1 -gt 80 ]
then
        echo "$IP服务器 /根分区 使用率已经超过80%,请及时处理。 ">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
echo "$IP服务器 /根分区 使用率已经超过80%,请及时处理。 " | mail -s "$IP服务器硬盘告警" denghj@belrare.com
 
else
        echo "$IP服务器 /根分区 使用率为$disk_sda1%,使用率正常">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
fi
 
 
 
 
 
#取当前用户登录数
users=`uptime |awk '{print $6}'`
 
#设置登录用户个数告警数为3个，如果当前用户超过3个，立即发邮件告警
if [ $users -gt 2 ]
then
echo "$IP服务器用户数已经达到$users个，请及时处理。">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
echo "$IP服务器用户数已经达到$users个，请及时处理。" | mail -s "$IP服务器用户登录数告警" denghj@belrare.com
else
echo "$IP服务器当前登录用户为$users个，情况正常">>/usr/monitor/performance/performance_$(date +%Y%m%d).log
fi