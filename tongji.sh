#!/bin/bash`
#
R1=`cat /sys/class/net/eth0/statistics/rx_bytes`
T1=`cat /sys/class/net/eth0/statistics/tx_bytes`
 
sleep 1
 
R2=`cat /sys/class/net/eth0/statistics/rx_bytes`
T2=`cat /sys/class/net/eth0/statistics/tx_bytes`
 
TBPS=`expr $T2 - $T1`
RBPS=`expr $R2 - $R1`
 
TKBPS=`expr $TBPS / 1024`
RKBPS=`expr $RBPS / 1024`
 
echo "上传速率 eth0: $TKBPS kb/s 下载速率 eth0: $RKBPS kb/s at $(date +%Y%m%d%H:%M:%S)" >>/usr/monitor/network/network_$(date +%Y%m%d).log
 
 
当天最高上传及下载流量统计
 
TX=0;
RX=0;
 
MAX_TX=0;
MAX_RX=0;
 
while read line
do
        a=`echo $line | grep "eth0" |awk '{print $3}'`
 
if [ $a -ge 0 ]
then
        TX=$a
        if [ $TX -ge $MAX_TX ]
        then
                MAX_TX=$TX
        fi
fi
 
        b=`echo $line | grep "eth0" |awk '{print $7}'`
 
if [ $b -ge 0 ]
then
        RX=$b
        if [ $RX -ge $MAX_RX ]
        then
                MAX_RX=$RX
        fi
fi
done < /usr/monitor/network/network_$(date +%Y%m%d).log
 
echo "最高上传速度为 $MAX_TX kb/s at $(date +%Y%m%d)">>/usr/monitor/network/tongji.log
 
echo "最高下载速度为 $MAX_RX kb/s at $(date +%Y%m%d)">>/usr/monitor/network/tongji.log