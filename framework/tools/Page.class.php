<?php
namespace framework\tools;
/**
 * 分页类
 */
class Page
{
    //总的记录数
    private $total_rows = 100;
    //每页显示的数量, 默认每页显示5条数据
    private $pagesize = 15;

    //当前是第几页
    private $now_page = 3;

    //点击超链接时，跳转的页面
    private $url = '';

    public function __set($name, $value)
    {
        if(property_exists($this,$name)){
            $this -> $name = $value;
        }
    }
    public function __get($name)
    {
        if(property_exists($this,$name)){
            return $this -> $name;
        }
    }

    //该核心方法给我们返回一个具有class="pagination"的ul
    public function create()
    {
        $url = $this -> url ."&page=";
        $first = 1;

        $first_active = $this->now_page == 1 ? 'active' :'';
        //创建首页的标签
        $page_html = <<<HTML
        <ul class="pagination">
            <li class="$first_active"><a href="{$url}{$first}">首页</a></li>
HTML;

        //创建中间的页码数,该创建多少取决于总共有多少页

        $count = ceil($this -> total_rows / $this ->pagesize);
        //从当前页-$this -> pagesize开始，到当前页+$this -> pagesize结束
        for($i=$this->now_page-$this -> pagesize;$i<=$this->now_page+$this -> pagesize;$i++){
            $active = $this -> now_page == $i ? 'active' : '';
            if($i < 2 || $i > $count-1){
                continue;
            }
            $page_html .= <<<HTML
            <li class="$active"><a href="{$url}{$i}">$i</a></li>
HTML;
        }

        //创建尾页的标签
        $last_active = $this -> now_page == $count ? 'active' :'';
        $page_html .= <<<HTML
            <li class="$last_active"><a href="{$url}{$count}">尾页</a></li>
        </ul>
HTML;
        //返回拼接好的ul
        return $page_html;
    }
}