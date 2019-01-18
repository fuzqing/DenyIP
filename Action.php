<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class DenyIP_Action extends Typecho_Widget implements Widget_Interface_Do
{
	public function init()
	{
		$user = Typecho_Widget::widget('Widget_User');
        $user->pass('administrator');
        $this->options = Typecho_Widget::widget('Widget_Options');
		$this->get_contents = file_get_contents(__DIR__.'/denyip.conf');
		$result = preg_match_all('/deny\s(.*?);/',$this->get_contents,$all_ip);
		$this->denyips = $result ? $all_ip[1] : [];
	}
	
    public function action()
    {
		$this->init();
        $this->on($this->request->is('do=denyone'))->denyOne();
        $this->on($this->request->is('do=denyall'))->denyAll();
		$this->on($this->request->is('do=delete'))->deleteIP();
        exit;
    }

    public function denyAll()
    {
        try {
            $data = @file_get_contents('php://input');
            $data = Json::decode($data, true);
            $data = array_unique($data);
            if (!is_array($data)) {
                throw new Exception('params invalid');
            }
            $denyips = $this->denyips;
            $msg = '';
            foreach ($data as $denyip) {
                if (!filter_var($denyip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                    $msg .= 'IP地址：'.$denyip.' 不合法'.PHP_EOL;
                } else {
                    if(in_array($denyip,$denyips)){
                        $msg .= 'IP地址：'.$denyip.' 已存在'.PHP_EOL;
                    } else {
                        $contents = 'deny '.$denyip.';';
                        file_put_contents(__DIR__.'/denyip.conf',$contents.PHP_EOL,FILE_APPEND);
                        array_push($denyips,$denyip);
                        $msg .= 'IP地址：'.$denyip.' 禁访成功'.PHP_EOL;
                    }

                }
            }
            $this->updateDenyIPJSONFile($denyips);
            $response = array(
                'code' => 0,
                'msg' => $msg
            );

        } catch (Exception $e) {
            $response = array(
                'code' => 100,
                'msg' => $e->getMessage(),
            );
        }

        $this->response->throwJson($response);
    }

    public function denyOne()
    {
        $denyip = trim($_POST['denyip']);
        if (!filter_var($denyip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            echo json_encode(['code'=>1,'msg'=>'大佬，请填写正确的IP地址！']);
            return;
        } else {
            if(in_array($denyip,$this->denyips)){
                echo json_encode(['code'=>2,'msg'=>'禁访IP：'.$denyip.' 已存在！']);
                return;
            }
            $contents = 'deny '.$denyip.';';
            file_put_contents(__DIR__.'/denyip.conf',$contents.PHP_EOL,FILE_APPEND);
            $denyips = $this->denyips;
            array_push($denyips,$denyip);
            $this->updateDenyIPJSONFile($denyips);
            echo json_encode(['code'=>0,'msg'=>'禁访IP：'.$denyip.' 成功！']);
            return;
        }
    }

    public function deleteIP()
    {
		$denyip = trim($_POST['denyip']);
		if (!filter_var($denyip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			echo json_encode(['code'=>1,'msg'=>'大佬，请填写正确的IP地址！']);
            return;
		} else {
            if(!in_array($denyip,$this->denyips)){
                echo json_encode(['code'=>2,'msg'=>'禁访IP：'.$denyip.' 不存在！']);
            	return; 
            }
			$contents = 'deny\s'.$denyip.';'.PHP_EOL;
            $this->get_contents = preg_replace("/$contents/",'',$this->get_contents);
			file_put_contents(__DIR__.'/denyip.conf',$this->get_contents);
			$denyips = $this->denyips;
            $key = array_search($denyip,$denyips);
            if($key !== false){
				unset($denyips[$key]);
                $this->updateDenyIPJSONFile($denyips);
			}
            echo json_encode(['code'=>0,'msg'=>'删除禁访IP：'.$denyip.' 成功！']);
			return;
		}
    }
  
    public function updateDenyIPJSONFile($denyips)
    {
		file_put_contents(__DIR__.'/denyip.json',json_encode($denyips));
    }
}
