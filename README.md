<h1 align="center">🌿ADrawLottery🌿</h1>
    开源免费的Typecho评论抽奖插件

### 安装说明

1.将插件上传到**/usr/plugins/，并重命名为**ADrawLottery**，请保证该目录有读写权限。

2.修改主题目录里的functions.php，在合适的位置加入挂载点以添加自定义字段
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

### 插件主题适配说明

本插件测试环境为原生typecho主题
由于很多第三方主题修改了许多文章或字段文件，你需要对其作出对应微调

例如：MyDiary主题对functions.php用了其他方式修改，你需要在该主题对应themeFields.php文件里的相应位置放入自定义字段代码，即安装说明第二步的代码。由于类似thefields.php或者其他主题对functions.php加入了自己的自定义字段，你只需要在它设置的自定义字段内添加本插件需要的字段就行，如下代码框，你只需要把我们的代码放入已有的themefields定义框架里就行，删掉本插件的框架

```php
function themeFields($layout) {
。。。（第三方主题定义内容）

。。。（本插件需要的自定义字段内容）

｝
```

同时，第三方主题未进行全面适配可能有一些小问题，可以来反馈（举例：MyDiary主题 创建文章并使用本插件后文章会乱码，因为主题误识别插件倒计时显示模块导致封面错误和不知名错误，只需要自定义一个封面图片就可解决）

### 更新计划

1.将插件采用在footer里显示倒计时和中奖信息的方式改为在文章中显示(已完成)

2.美化倒计时和中奖显示

### 更新日志


2023-08-07更新说明：
* 更新ADrawLottery 1.0.3 版本
* 将原先倒计时和中奖模块在footer里显示的方法修复为在文章标签上显示（目前只会在文章开头显示）

2023-08-06更新说明：
* ADrawLottery 1.0.2 版本已完成
* 修改beta版倒计时显示逻辑
* 修复beta版抽取其他文章评论的bug
* 修复beta版中重复执行抽奖和无法储存抽奖数据的bug

### 感谢 Thanks

- [即刻学术](https://www.ijkxs.com "技术支持")
