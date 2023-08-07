<?php
/**
 * Typecho抽奖插件
 * @package ADrawLottery
 * @author 柠宇
 * @version 1.0.3
 * @link https://sau.cc/
 */

class ADrawLottery_Plugin implements Typecho_Plugin_Interface
{
    public static $authorName = '柠宇'; // 作者名称
    public static $authorBlog = 'https://i.sau.cc/'; // 作者博客链接

    public static function activate()
    {
     Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array(__CLASS__, 'addCountdownToContent');
        
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
    

    public static function addCountdownToContent($content, $widget, $lastResult)
    {
    $options = Typecho_Widget::widget('Widget_Options');
    $enabled = $options->plugin('ADrawLottery')->ADrawLottery_enabled;
    $slug = $widget->slug; // 获取文章slug

    // 如果 $lastResult 不为空，则表示之前已经添加过倒计
    if ($enabled == '1') {
        // 使用正则表达式匹配 <!--ADrawLottery start--> 和 <!--ADrawLottery end--> 标签之间的内容
        preg_match('/<!--ADrawLottery start-->(.*?)<!--ADrawLottery end-->/s', $content, $matches);

        if (isset($matches[1])) {
            $drawLotteryContent = $matches[1];
            // 读取中奖信息
            $winner = self::readWinnerFromLog($slug);

            if ($winner) {
                // 输出中奖信息
                $drawLotteryContent .= self::outputWinner($winner);
            } else {
                // 显示倒计时
                $drawLotteryContent .= self::showCountdown($slug);
            }

            // 替换原来的标签内容为新的内容（包括倒计时或中奖信息）
            $content = str_replace($matches[0], $drawLotteryContent, $content);
        }
    }

    return $content;
    }
    
    
    private static function readWinnerFromLog($slug)
    {
        $file = __DIR__ . '/ADrawLottery_log.txt';
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $data = explode('##', $line);
                if ($data[0] == $slug) {
                    return array(
                        'author' => $data[1],
                        'avatar' => $data[2],
                        'content' => $data[3]
                    );
                }
            }
        }
        return false;
    }

    
    private static function getADrawLotteryTime($slug)
    {
        // 获取文章自定义字段中的抽奖执行时间
        $archive = Typecho_Widget::widget('Widget_Archive');
        $fields = $archive->fields;
        return $fields->drawlottery_time;
    }
    
    
    public static function showCountdown($slug)
    {
    $options = Typecho_Widget::widget('Widget_Options');
    $class = Typecho_Widget::widget('Widget_Archive');
    $ADrawLotteryTime = self::getADrawLotteryTime($slug);

    // 获取当前时间戳和抽奖执行时间的时间戳
    $currentTime = self::getBeijingTime();
    $drawTime = strtotime($ADrawLotteryTime);

    // 计算倒计时剩余秒数
    $countdown = $drawTime - $currentTime;

    // 如果倒计时小于等于0，则表示抽奖已经开始或结束
    if ($countdown <= 0) {
        // 倒计时结束，执行抽奖逻辑并显示中奖人名称、头像和评论内容
        self::doADrawLottery();
    } else {
        // 计算剩余天数、小时数、分钟数和秒数
        $days = floor($countdown / (60 * 60 * 24));
        $hours = floor(($countdown - $days * 60 * 60 * 24) / (60 * 60));
        $minutes = floor(($countdown - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
        $seconds = $countdown - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60;

        // 显示倒计时
        echo '<div style="border: 1px solid #ccc; padding: 10px; background-color: #f2f2f2;">';
        if ($days > 0) {
            echo '<p style="font-size: 18px;">距离抽奖还有 ' . $days . '天 ' . $hours . '小时 ' . $minutes . '分钟 </p>';
        } elseif ($hours > 0) {
            echo '<p style="font-size: 18px;">距离抽奖还有 ' . $hours . '小时 ' . $minutes . '分钟 </p>';
        } elseif ($minutes > 0) {
            echo '<p style="font-size: 18px;">距离抽奖还有 ' . $minutes . '分钟 </p>';
        } else {
            echo '<p style="font-size: 18px;">距离抽奖还有 ' . $seconds . '秒</p>';
        }
        echo '</div>';
    }
    }

    private static function getBeijingTime()
    {
        // 设置时区为东八区
        date_default_timezone_set('Asia/Shanghai');
        return time();
    }

    private static function hasADrawLotteryTag($content)
    {
    // 检查文章内容是否含有抽奖标签
    if (strpos($content, '<!--ADrawLottery start-->') !== false) {
        return true;
    }

    return false;
    }

    
    
    private static function outputWinner($winner)
    {
         echo '<div class="draw-lottery">';
         echo '<p>恭喜中奖！</p>';
         echo '<p>中奖人：' . $winner['author'] . '</p>';
         echo '<p>中奖人头像：<img src="' . $winner['avatar'] . '" alt="' . $winner['author'] . '"></p>';
         echo '<p>中奖评论：' . $winner['content'] . '</p>';
         echo '</div>';
    }
    
    
    private static function doADrawLottery()
    {
        
        
        $options = Typecho_Widget::widget('Widget_Options');
        $bloggerEmail = $options->plugin('ADrawLottery')->ADrawLottery_blogger_email;

        // 获取文章的所有评论并按评论时间从早到晚排序
        $cid = Typecho_Widget::widget('Widget_Archive')->cid;
        $db = Typecho_Db::get();
        

        
        $page = $db->fetchRow($db->select()->from('table.contents')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.cid = ?', $cid));
        $comments = $db->fetchAll($db->select()->from('table.comments')
            ->where('table.comments.status = ?', 'approved')
            ->where('table.comments.created < ?', self::getBeijingTime())
            ->where('table.comments.type = ?', 'comment')
            ->where('table.comments.cid = ?', $cid)
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

        // 将中奖信息写入文件
        $data = $cid . '##' . $author . '##' . $avatar . '##' . $content . PHP_EOL;
        $file = __DIR__ . '/ADrawLottery_log.txt';
        file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
            
        }
    }
}
