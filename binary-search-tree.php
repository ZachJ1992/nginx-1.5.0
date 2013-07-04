<?php
/** 二叉搜索树
 * 搜索树数据结构支持许多动态集合操作，包括SEARCH, MINIMUM, MAXIMUM, PREDECESSOR, SUCCESSOR, INSERT, DELETE等操作。
 * 即支持，搜索、最小值和最大值、前驱后继、插入和删除等操作。
 * 二叉搜索树是按二叉树来组织的。这种树可以用一个链表数据结构来表示，其中每个节点就是一个对象。
 * 二叉搜索树的结构(key, 卫星数据, left, right, p). 分别是关键字、卫星数据、左孩子、右孩子和双亲。
 * 
 * 二叉搜索树的性质:
 * 设x为二叉搜索树的一个节点:
 *    如果y是x的一个左子树的结点，那么y.key <= x.key
 *    如果z是x的一个右子树的节点，那么z.key >= x.key
 *
 * 下面先看看树的遍历方法:
 *    深度优先遍历(depth-first)
 *        先序遍历(pre-order): 先访问根元素，然后再访问孩子元素
 *            PREORDER-TREE-WALK(x)                  // 递归方式的伪代码
 *                if x != NIL
 *                    print x.key                    // 使用根做些有意义的事情
 *                    PREORDER-TREE-WALK(x.left)     // 遍历左子树
 *                    PREORDER-TREE-WALK(x.right)    // 遍历右子树
 *
 *            iterativePreorder(node)                // 迭代方式的伪代码
 *                parentStack = empty stack
 *                while not parentStack.isEmpty() or node != null
 *                    if node != null then
 *                        visit(node)
 *                        parentStack.push(node.right)
 *                        node = node.left
 *                    else
 *                        node = parentStack.pop()
 *
 *        中序遍历(in-order)
 *            INORDER-TREE-WALK(x)
 *                if x != NIL
 *                    INORDER-TREE-WALK(x.left)
 *                    print x.key
 *                    INORDER-TREE-WALK(x.right)
 *
 *            iterativeInorder(node)                 // 迭代方式的伪代码
 *                parentStack = empty stack
 *                while not parentStack.isEmpty() or node != null
 *                    if node != null then
 *                        parentStack.push(node)
 *                        node = node.left
 *                    else
 *                        node = parentStack.pop()
 *                        visit(node)
 *                        node = node.right
 * 
 *        后续遍历(post-order)
 *            POSTORDER-TREE-WALK(x)
 *                if x != NIL
 *                    POSTORDER-TREE-WALK(x.left)
 *                    POSTORDER-TREE-WALK(x.right)
 *                    print x.key
 *
 *            iterativePostorder(node)
 *                if node == null then return
 *                nodeStack.push(node)
 *                prevNode = null
 *                while not nodeStack.isEmpty()
 *                    currNode = nodeStack.peek()
 *                    if prevNode == null or prevNode.left == currNode or prevNode.right == currNode
 *                        if currNode.left != null
 *                            nodeStack.push(currNode.left)
 *                        else if currNode.right != null
 *                            nodeStack.push(currNode.right)
 *                        else if currNode.left == prevNode
 *                            if currNode.right != null
 *                                nodeStack.push(currNode.right)
 *                        else
 *                            visit(currNode)
 *                            nodeStack.pop()
 *                     prevNode = currNode
 *    广度优先遍历(breadth-first)
 *        广度优先周游二叉树(层序遍历)是用队列来实现的，从二叉树的第一层（根结点）开始，
 *        自上至下逐层遍历；在同一层中，按照从左到右的顺序对结点逐一访问。
 *        按照从根结点至叶结点、从左子树至右子树的次序访问二叉树的结点。算法：
 *            1) 初始化一个队列，并把根结点入列队；
 *            2) 当队列为非空时，循环执行步骤3到步骤5，否则执行6；
 *            3) 出队列取得一个结点，访问该结点；
 *            4) 若该结点的左子树为非空，则将该结点的左子树入队列；
 *            5) 若该结点的右子树为非空，则将该结点的右子树入队列；
 *            6) 结束。
 *        levelorder(root)
 *            q = empty queue
 *            q.enqueue(root)
 *            while not q.empty do
 *                node := q.dequeue()
 *                visit(node)
 *                if node.left ≠ null
 *                    q.enqueue(node.left)
 *                if node.right ≠ null
 *                    q.enqueue(node.right)
 非递归深度优先遍历二叉树。

栈是实现递归的最常用的结构，利用一个栈来记下尚待遍历的结点或子树，以备以后访问，可以将递归的深度优先遍历改为非递归的算法。

1. 非递归前序遍历：遇到一个结点，就访问该结点，并把此结点推入栈中，然后下降去遍历它的左子树。遍历完它的左子树后，从栈顶托出这个结点，并按照它的右链接指示的地址再去遍历该结点的右子树结构。

2. 非递归中序遍历：遇到一个结点，就把它推入栈中，并去遍历它的左子树。遍历完左子树后，从栈顶托出这个结点并访问之，然后按照它的右链接指示的地址再去遍历该结点的右子树。

3. 非递归后序遍历：遇到一个结点，把它推入栈中，遍历它的左子树。遍历结束后，还不能马上访问处于栈顶的该结点，而是要再按照它的右链接结构指示的地址去遍历该结点的右子树。遍历遍右子树后才能从栈顶托出该结点并访问之。另外，需要给栈中的每个元素加上一个特征位，以便当从栈顶托出一个结点时区别是从栈顶元素左边回来的(则要继续遍历右子树)，还是从右边回来的(该结点的左、右子树均已周游)。特征为Left表示已进入该结点的左子树，将从左边回来；特征为Right表示已进入该结点的右子树，将从右边回来。

4. 简洁的非递归前序遍历：遇到一个结点，就访问该结点，并把此结点的非空右结点推入栈中，然后下降去遍历它的左子树。遍历完左子树后，从栈顶托出一个结点，并按照它的右链接指示的地址再去遍历该结点的右子树结构。

图的深度优先搜索法是树的先根遍历的推广，它的基本思想是：从图G的某个顶点v0出发，访问v0，然后选择一个与v0相邻且没被访问过的顶点vi访问，再从vi出发选择一个与vi相邻且未被访问的顶点vj进行访问，依次继续。如果当前被访问过的顶点的所有邻接顶点都已被访问，则退回到已被访问的顶点序列中最后一个拥有未被访问的相邻顶点的顶点w，从w出发按同样的方法向前遍历，直到图中所有顶点都被访问。
    图的广度优先搜索是树的按层次遍历的推广，它的基本思想是：首先访问初始点vi，并将其标记为已访问过，接着访问vi的所有未被访问过的邻接点vi1,vi2, …, vi t，并均标记已访问过，然后再按照vi1,vi2, …, vi t的次序，访问每一个顶点的所有未被访问过的邻接点，并均标记为已访问过，依次类推，直到图中所有和初始点vi有路径相通的顶点都被访问过为止。

外排序归并实现:
http://www.cnblogs.com/huangxincheng/archive/2012/12/19/2824943.html
http://blog.chinaunix.net/uid-28732386-id-3529361.html
http://www.cnblogs.com/this-543273659/archive/2011/07/30/2122083.html
http://kenby.iteye.com/blog/1017532
http://diducoder.com/mass-data-topic-9-external-sort.html
http://zh.wikipedia.org/wiki/外排序
 */