<?php
    // error_reporting(0);
    class du4t_waf{
        private $ip;
        private $headers;
        private $request_url;
        private $request_data;
        private $request_method;

        public function get_message() 
        {
            // 获取服务器ip
            if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
                $ip = getenv('REMOTE_ADDR');
            } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
            $this->ip=$res;

            //获取header 
            $this->headers = getallheaders();  
	        foreach ($this->headers as $key => $val)
            {
                if ($val == "")
                {
                    unset($this->headers[$key]);
                }

                // 获取url
                $this->request_url = urldecode($_SERVER['REQUEST_URI']);

                // 获取post
                $this->request_data = file_get_contents('php://input');	

                // 获取请求方式
                $this->request_method=$_SERVER['REQUEST_METHOD'];
	        }

        }

        private function forward()
        {
            // $target_url=$this->headers['Host'].$this->request_url; // 自动反打来源ip
            $target_url="http://localhost:5001".$this->request_url;
            foreach($this->headers as $key=>$value)
            {
                $option['http']=array(
                    "timeout"=>60,
                    "method"=>$this->request_method,
                    "header"=>$option['http']['header']."\r\n".$key.":".$value, // 此处自动遍历太慢 可能需要手动添加header
                    "content"=>$this->request_data
                );
            }
            $context = stream_context_create($option);
            $result = file_get_contents($target_url, false, $context);
            // echo $option['http']['header'];
            echo $result;
            // 截取flag
            file_put_contents("/tmp/flag.txt",date('Y-m-d h:i:s', time())." ".$result.PHP_EOL,FILE_APPEND);
            // 保存http对象 方便后续直接调用重发
            file_put_contents("/tmp/objetct.txt",date('Y-m-d h:i:s', time())."---------------".PHP_EOL.serialize($option).PHP_EOL,FILE_APPEND);
            // 这里die感觉处理的不太好 可以修改
            die();
        }

        private function attack($target_url)
        {
            $payload=""; // 此处粘贴序列化的http对象
            $option=unserialize($payload);
            $context = stream_context_create($option);
            $result = file_get_contents($target_url, false, $context);
        }

        public function __construct()
        {
            $this->get_message();
            $this->forward();
            // var_dump($this->headers);
        }
    }
    $test=new du4t_waf();
    // $url="http://du4t.cn";
    // $options['http'] = array(
    //     'timeout'=>60,
    //     'method' => 'POST',
    //     'header' => 'Content-type:application/x-www-form-urlencoded',
    //     'content' => $query
    //    );
    // $context = stream_context_create($options);
    // // echo $context;
    // $result = file_get_contents($url, false, $context);
    