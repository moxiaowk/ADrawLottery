<?php
/**
 * Typecho抽奖插件
 * @package ADrawLottery
 * @author 柠宇
 * @version 1.0.0
 * @link https://sau.cc/
 */

class ADrawLottery_Plugin implements Typecho_Plugin_Interface
{
    public static $authorName = '柠宇'; // 作者名称
    public static $authorBlog = 'https://sau.cc/'; // 作者博客链接

    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
    }

    public static function deactivate()
    {
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $enabled = new Typecho_Widget_Helper_Form_Element_Radio(
            'ADrawLottery_enabled',
            array('1' => _t('是'), '0' => _t('否')),
            '0', _t('是否启用抽奖功能'), _t('选择“是”表示启用抽奖功能，选择“否”表示不启用抽奖功能')
        );

        $bloggerEmail = new Typecho_Widget_Helper_Form_Element_Text('ADrawLottery_blogger_email', NULL, NULL, _t('博主邮箱'), _t('请输入博主邮箱'));

        $form->addInput($enabled);

        // 添加作者名称和作者博客链接的静态信息显示
        $authorInfo = '<p><strong>' . _t('By：') . '</strong><a href="' . self::$authorBlog . '">' . self::$authorName . '</a></p>';
            
        echo '<div class="typecho-item">' . $authorInfo . '</div>';

        $form->addInput($bloggerEmail);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function render()
    {
    }

    public static function footer()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $enabled = $options->plugin('ADrawLottery')->ADrawLottery_enabled;

        if ($enabled == '1') {
            $slug = Typecho_Widget::widget('Widget_Archive')->slug; // 获取文章slug
            if (!empty($slug) && self::hasADrawLotteryTag()) {
                // 如果存在抽奖标签且满足其他条件，则在页面底部显示抽奖倒计时
                self::showCountdown();
            }
        }
    }

    public static function showCountdown()
    {
        $class = Typecho_Widget::widget('Widget_Archive');
        $ADrawLotteryTime = $class->fields->drawlottery_time;

        // 获取当前时间戳和抽奖执行时间的时间戳
        $currentTime = self::getBeijingTime();
        $drawTime = strtotime($ADrawLotteryTime);

        // 计算倒计时剩余分钟数
        $countdown = $drawTime - $currentTime;
        $countdownMinutes = ceil($countdown / 60);

        // 显示倒计时
        echo '<div style="border: 1px solid #ccc; padding: 10px; background-color: #f2f2f2;">';
        echo '<p style="font-size: 18px;">距离抽奖结束还有 ' . $countdownMinutes . ' 分钟</p>';
        echo '</div>';
         // 判断是否到达抽奖执行时间
        if ($countdown <= 0) {
            // 倒计时结束，执行抽奖逻辑并显示中奖人名称、头像和评论内容
            self::doADrawLottery();
        }
    
    }
    private static function getBeijingTime()
    {
        // 设置时区为东八区
        date_default_timezone_set('Asia/Shanghai');
        return time();
    }

    private static function hasADrawLotteryTag()
    {
        // 获取当前文章内容
        $content = Typecho_Widget::widget('Widget_Archive')->content;

        // 检查文章内容是否含有抽奖标签
        if (strpos($content, '<!--ADrawLottery start-->') !== false) {
            return true;
        }

        return false;
    }



    private static function doADrawLottery()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $bloggerEmail = $options->plugin('ADrawLottery')->ADrawLottery_blogger_email;

        // 获取文章的所有评论并按评论时间从早到晚排序
        $db = Typecho_Db::get();
        $slug = Typecho_Widget::widget('Widget_Archive')->parameter->slug;
        $page = $db->fetchRow($db->select()->from('table.contents')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.slug = ?', $slug));
        $comments = $db->fetchAll($db->select()->from('table.comments')
            ->where('table.comments.status = ?', 'approved')
            ->where('table.comments.created < ?', self::getBeijingTime())
            ->where('table.comments.type = ?', 'comment')
            // ->where('table.comments.cid <> ?', $page['authorId'])
            ->order('table.comments.created', Typecho_Db::SORT_ASC));

        // 过滤博主评论
        foreach ($comments as $key => $comment) {
            if ($comment['mail'] == $bloggerEmail) {
                unset($comments[$key]);
            }
        }

        // 随机选择一个楼层的评论显示中奖人名称、头像和评论内容
        $winner = null;
        if (!empty($comments)) {
            $winner = $comments[array_rand($comments)];
        }

        if ($winner) {
            $author = $winner['author'];
            $avatar = Typecho_Common::gravatarUrl($winner['mail'], 80, 'X', 'mm', Typecho_Widget::widget('Widget_Options')->siteUrl);
            $content = $winner['text'];
            echo '<div class="draw-lottery">';
            echo '<p>恭喜中奖！</p>';
            echo '<p>中奖人：' . $author . '</p>';
            echo '<p>中奖人头像：<img src="' . $avatar . '" alt="' . $author . '"></p>';
            echo '<p>中奖评论：' . $content . '</p>';
            echo '</div>';
        }
    }
}
