<h1 align="center">🌿ADrawLottery🌿</h1>
    开源免费的Typecho评论抽奖插件

### 安装说明

1.将插件上传到**/usr/plugins/，并重命名为**ADrawLottery**，请保证该目录有读写权限。

2.修改functions.php，在合适的位置加入挂载点以添加自定义字段
```php
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
```

3.在后台插件设置配置好相关信息，插件的总抽奖开关和博主邮箱--便于过滤博主评论

4.请在文章合适位置插入以下代码（原计划插入后在文章插入处显示倒计时和中奖信息模块，暂时未实现）

```php
<!--ADrawLottery start-->
<!--ADrawLottery end-->
```

5.确认以上部分已完成，然后在需要开启抽奖的文章自定义字段设置好是否开启抽奖和具体开奖时间


### 更新计划

1.将插件采用在footer里显示倒计时和中奖信息的方式改为在文章中显示

2.美化倒计时和中奖显示

### 更新日志

2023-08-06更新说明：
* ADrawLottery 1.0版本已完成
* 修改beta版倒计时显示逻辑
* 修复beta版抽取其他文章评论的bug
* 修复beta版中重复执行抽奖和无法储存抽奖数据的bug

### 感谢 Thanks

- [即刻学术](https://www.ijkxs.com "部分技术支持")
