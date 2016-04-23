<?php
class lrc{
	private $repeatCount 	= 1; //重复次数
	private $dur 			= '20s'; //持续时间
	private $begin 			= '1s'; //开始时间
	private $attributeName 	= 'y'; //方向 沿着哪个轴运动
	public  $lrcUrl 		= '' ; //歌词的地址
    private $delay          = 3;    //歌词延迟播放5秒
    private $map            = ['x'=>200,'y'=>500];
    private $left           = 0.1;
    private $style          = 'box-sizing: border-box;background:#222;fill:#fc0;text-align:center;';   //特定样式
    public $delayHtml       = '';   //延迟部分代码
    public $lrc             = '';
	public function __construct(){
        $this->lrc = '<svg style="'.$this->style.'" xmlns="http://www.w3.org/2000/svg" height="'.$this->map['x'].'" width="'.$this->map['y'].'">';
        $this->buildDelay();
        $this->lrc .= $this->delayHtml;
    }
    public function showLrc($lrc_url)
    {
        $array = $this->buildLrcArray($lrc_url);
        $this->lrc .= $this->buildLrc($array);
        return $this->lrc .= '</svg>';
    }

	private function buildLrcArray($lrc_url)
	{
        $lrc = '';
	    $content = @file_get_contents($lrc_url);
        if($content){
            $array = explode("\n", $content);// 按”回车换行“将歌词切割成数组
            $lrc = [];
      
            foreach($array as $val){
                $val = preg_replace('/\r/', '', $val);// 清除掉”回车不换行“符号
                preg_match_all('/\[\d{2}\:\d{2}\.\d{2}\]/', $val, $matches);// 正则匹配歌词时间
                if( !empty($matches[0]) ){
                    $data_plus = "";
                    $time_array = [];
                    // 将可能匹配的多个时间挑选出来，例如：[00:21]、[03:40]
                    foreach($matches[0] as $V){
                        $data_plus .= $V;
                        $V = str_replace("[", "", $V);
                        $V = str_replace("]", "", $V);
                        $date_array = explode(":", $V);
                        
                        $time_array[] =  $date_array[0]*60 + $date_array[1];//转换成秒
                    }
                    // 将上面的得到的时间，例如：[00:21][03:40]，替换成空，得到歌词
                    $data_plus = str_replace($data_plus, "", $val);   
                       
                    // 将时间和歌词组合到数组中
                    foreach($time_array as $key => $V){
                    	if(empty(trim($data_plus))){
                    		unset($time_array[$key]); //删除空行
                    		continue;
                    	}
                        $lrc[] = [$V, $data_plus];   
                    }   
                }   
            }   
             
            $lrc = $this->bsort($lrc);// 按时间顺序来排序数组
        }
      return  $lrc;
}

    /**
    *构建delay部分提示文字
    */
    private function buildDelay()
    {
        //构建延迟模板
        $delayTemplate  = '<text x="'.($this->map['y']*$this->left).'" y="'.($this->map['x']+50).'">%s秒后开始歌词<animate repeatCount="1" dur="%us" to="0" from="'.($this->map['x']+50).'" attributeName="y" begin="%ss" /></text>';

        for ($i=0; $i < $this->delay; $i++) { 
             $this->delayHtml .= sprintf($delayTemplate, ($this->delay-$i), $this->delay, $i).PHP_EOL;
        }
    }

    private function buildLrc($array)
    {
        $lrcMain = '';
        $lrcTemplate = '<text x="'.($this->map['y']*$this->left).'" y="'.($this->map['x']+50).'">%s<animate repeatCount="1" dur="'.$this->dur.'" to="0" from="'.($this->map['x']+50).'" attributeName="y" begin="%ss" /></text>';
         
        foreach($array as $key => $value){
            $value[0] +=$this->delay; //添加歌词延迟
            $begin = ceil($value[0]-3);
            $lrcMain .= sprintf($lrcTemplate, $value[1], $begin).PHP_EOL;
        }
        return $lrcMain; 
    }
  
	/**
    * 按时间顺序来排序数组
    */ 
	private function bsort(array $array){
	    $count = count($array);
	    for($i=0; $i<$count; $i++){
	        for($j=$count-1; $j>$i; $j--){
	            if($array[$j][0] < $array[$j-1][0]){
	                $temp = $array[$j];
	                $array[$j] = $array[$j-1];
	                $array[$j-1] = $temp;
	            }
	        }
	    }
	    return $array;
	}

}

$lrc = new lrc();
echo $result = $lrc->showLrc('隐形的翅膀.lrc');
 
?>
  
 
 
  
 
