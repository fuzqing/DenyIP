<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 禁止IP访问插件
 * 
 * @package DenyIP
 * @author Fuzqing
 * @version 1.0.0
 * @link https://huangweitong.com
 *
 */
class DenyIP_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
		Helper::addPanel(1, 'DenyIP/deny-ip.php', '禁访IP', '管理禁访IP', 'administrator');
		Helper::addAction('denyip', 'DenyIP_Action');
        Typecho_Plugin::factory('admin/footer.php')->end = [__CLASS__, 'render'];
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
	{
		Helper::removeAction('denyip');
		Helper::removePanel(1, 'DenyIP/deny-ip.php');
	}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

    public static function render()
    {
        $url = $_SERVER['REQUEST_URI'];
        if(strstr($url,'extending.php?panel=Access%2Fpage%2Fconsole.php') || strstr($url,'extending.php?panel=Access/page/console.php')) {

            $options = Typecho_Widget::widget('Widget_Options');
            $deny_ip_url = Typecho_Common::url('/index.php/action/denyip?do=denyall', $options->siteUrl);
            $get_denyips = file_get_contents(__DIR__.'/denyip.json');
            $script = <<<SCRIPT
        <script>
         $(document).ready(function(){
            var denyips = '{$get_denyips}';
            $('.typecho-table-wrap td a[data-ip]').each(function(index,elem) {
               if(denyips.indexOf($(elem).text()) != -1) {
                   
                   $(elem).css('color','red').next().after('<span style="color:red"> 已禁访 </span>');
               }
            });
            var deny = '<li><a id="deny" href="#">禁访</a></li>';
            $(".dropdown-menu").append(deny);
               $('.dropdown-menu #deny').click(function() {
                    swal({
                      title: '你确定?',
                      text: '你确认要禁访这些IP吗?',
                      type: 'warning',
                      showCancelButton: true,
                      confirmButtonColor: '#DD6B55',
                      confirmButtonText: '是的',
                      cancelButtonText: '算啦',
                      closeOnConfirm: false
                    }, function() {
                        var ips = [];
                        $('.typecho-list-table input[type="checkbox"]').each(function(index, elem) {
                            if (elem.checked) {
                                ips.push($($(elem).parent('td').siblings()[2]).find('a')[0].text);
                            }
                        });
                        if (ips.length == 0) {
                            return swal('错误', '你并没有勾选任何内容', 'warning');
                        }
                        $.ajax({
                            url: '{$deny_ip_url}',
                            method: 'post',
                            dataType: 'json',
                            contentType: 'application/json',
                            data: JSON.stringify(ips),
                            success: function(data) {
                                if (data.code == 0) {
                                    swal({title:'禁访IP', text:data.msg, type:'success'},function() {
                                      window.location.reload();
                                    })
                                } else {
                                    swal('错误', data.msg, 'warning');
                                }
                            }

                        });
                });
                var _this = $(this);
                _this.parents('.dropdown-menu').hide().prev().removeClass('active');
            });
           });
        </script>
SCRIPT;
            echo $script;
        } else {
            return;
        }

    }
}
