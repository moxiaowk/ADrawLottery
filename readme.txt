本插件由柠宇开发，网站：https://sau.cc
上传到插件目录解压，并将文件夹名改为ADrawLottery，请保证该目录有读写权限。
请在文章合适位置插入
<!--ADrawLottery start-->

<!--ADrawLottery end-->

请在后台插件设置配置好相关信息




// 在主题的functions.php文件中添加自定义字段
function themeFields($layout) {
    $drawEnabled = new Typecho_Widget_Helper_Form_Element_Radio(
        'drawlottery_enabled',
        array('1' => _t('是'), '0' => _t('否')),
        '0', _t('是否启用抽奖功能'), _t('选择“是”表示启用抽奖功能，选择“否”表示不启用抽奖功能')
    );
    $drawTime = new Typecho_Widget_Helper_Form_Element_Text(
        'drawlottery_time', NULL, NULL,
        _t('抽奖执行时间'), _t('请输入抽奖执行时间，格式为：2002-11-11 11:11')
    );
    
    $layout->addItem($drawEnabled);
    $layout->addItem($drawTime);
}
