<?php
/** 最大堆
 * 最大堆是可以看成一个数组
 * 因为堆是以1开头的，那么堆中第i个元素对应数组中的第i+1个元素
 * 
 * 对于堆， 元素i有:
 *    left(i) = 2i
 *    right(i) = 2i + 1
 *    parent(i) = floor(i/2)      向下取整
 *
 * 那么这里是PHP数组中相应关系
 *    left(i) = (2i) + 1           = 2i + 1
 *    right(i) = (2i + 1) + 1      = 2i + 2
 *    parent(i) = floor((i-1)/2)
 * 这里主要实现最大堆的下面几种基本操作:
 * 1) 将给定数组初始化为最大堆
 * 2) 最大堆排序
 * 3) 返回最大元素
 * 4) 删除并返回最大值
 * 5) 删除元素
 * 6) 增加给定元素的值为key, 这里key比原始值要大
 * 7) 添加元素key
 */
//               0  1  2  3  4  5  6
//$array = array(9, 8, 4, 6, 5, 7, 3,);
$array = array(12, 34, 45, 17, 88, 99, 20, 35, 78, 89);
$arrayLength = sizeof($array);
print_r($array);
echo "<br />";
//buildMaxHeap($array, sizeof($array));
buildMaxHeap($array, $arrayLength);
print_r($array);
echo "<br />";
//heapsort($array, $arrayLength);
//print_r($array);
//echo "<br />";

//echo maxheapExtractMax($array);
//print_r($array);
//echo "<br />";
maxheapIncreaseKey($array, 3, 200);
print_r($array);
echo "<br />";

maxheapInsert($array, 180);
print_r($array);
echo "<br />";
/**********************************最大堆排序相关函数****************************************/
/**
 * 将数组$array的给定位置i处最大堆化
 * $i [0, n-1], n = sizeof($array)
 */
function maxHeapify(&$array, $i, $heapSize)
{
    $left = $i * 2 + 1;
    $right = $i * 2 + 2;
    //echo $left;
    //echo '--';
    //echo $right;
    // 找出给定元素i和其左右子节点中最大的元素，位置记录在$largest中
    if($left < $heapSize && $array[$left] > $array[$i])
    {
        $largest = $left;
    } else {
        $largest = $i;
    }

    if($right < $heapSize && $array[$right] > $array[$largest])
    {
        $largest = $right;
    }
    //echo $largest;
    // 如果$largest 与 给定的 $i值不相同， 即左右子孩子比这个节点大，需要下沉该节点
    if($largest != $i)
    {
        // 将该元素与最大值节点交换值
        swap($array[$i], $array[$largest]);
        maxHeapify($array, $largest, $heapSize);
    }
}

/**
 * 根据给定的数组构建最大堆
 */
function buildMaxHeap(&$array, $heapSize)
{
    // 最大堆中A[1,2,...,n]中的元素A[ceil(n/2)+1,...n]都是叶子节点， 即最多有ceil(n/2)非叶子节点
    // 那么我们就从堆中最后一个非叶子节点开始，直到根节点， 进行最大堆化
    $i = floor($heapSize / 2) - 1; // 数组对应堆中的最后一个非叶子节点
    for(; $i >= 0; $i--)
    {
        maxHeapify($array, $i, $heapSize);
    }
}

/**
 * 堆排序
 * 1) 将数组拿来构建最大堆
 * 2) 最大元素为堆的根， 将堆的最后元素与第一个元素交换，分割为未排序区域A[0,1,...,n-2], 和 A[n-1], 此时无序堆中的元素的key均比有序序列中的key小
 * 3) 然后将堆的大小减1，然后将堆元素的根进行最大堆化
 * 4) 重复2~3步骤， 然后直到堆中只剩下一个元素为止。 完成数组排序
 * 最大堆排序是属于原地排序的， 辅助空间O(1)
 */
function heapsort(&$array, $heapSize)
{
    buildMaxHeap($array, $heapSize);
    for($i = $heapSize - 1; $i > 0; $i--)
    {
        swap($array[0], $array[$i]);
        $heapSize--;
        maxHeapify($array, 0, $heapSize);
    }
}

/************************ 优先级队列(最大优先级队列) 相关函数*****************************/
/**
 * 获取最大堆$array的最大值
 * @param $array 最大堆
 * @return $array[0]
 */
function maxheapMax($array)
{
    return $array[0];
}

/**
 * 移除并返回最大堆的最大值，并维持最大堆性质
 *    将最大堆的最后一个元素赋值给根元素， 同时将最后一个元素去掉
 *    然后将最大堆以元素0重新最大堆化，因为这个新的根有可能需要下沉到合适位置
 * @param $array 最大堆
 * @return max of $array
 */
function maxheapExtractMax(&$array)
{
    $heapSize = sizeof($array);
    if($heapSize < 1)
        die("heap underflow!");
    $max = $array[0];
    $array[0] = $array[$heapSize-1];       //将堆中最后一个元素赋值给最大堆的根
    unset($array[$heapSize - 1]);
    $heapSize--;
    maxHeapify($array, 0, $heapSize);
    return $max;
}

/**
 * 将最大堆的给定元素增加键为新的值$key, 这里给定的$key值不能比原始值小。
 * 增大给定元素后， 可能使得序列不满足最大堆的性质， 需要通过元素的上浮，满足最大堆的性质
 * @param $array 最大堆序列
 * @param $i 要更新的元素的位置
 * @param $key 要更新的key值
 * @return 
 */
function maxheapIncreaseKey(&$array, $i, $key)
{
    if($key < $array[$i])
    {
        die("new key is smaller than current key");
    }
    $array[$i] = $key;
    while($i > 0 && $array[floor(($i - 1)/2)] < $array[$i])
    {
        swap($array[floor(($i - 1)/2)], $array[$i]);
        $i = floor(($i - 1)/2); // assign to parent position
    }
}

/**
 * 向最大堆中插入元素
 * 首先在最大堆的末尾插入足够小的元素， 维持最大堆特性
 * 然后将最后一个元素增加到给定$key, 调用上面的方法
 * @param $array 最大堆
 * @param $key 要插入最大堆的关键字值
 */
function maxheapInsert(&$array, $key)
{
    $heapSize = sizeof($array);
    $array[$heapSize] = -1;
    maxheapIncreaseKey($array, $heapSize, $key);
}
/******************************************辅助函数********************************************************/
/**
 * 交换两个元素值的函数
 */
function swap(&$a, &$b)
{
    $tmp = $a;
    $a = $b;
    $b = $tmp;
}