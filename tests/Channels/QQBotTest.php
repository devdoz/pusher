<?php declare(strict_types=1);

/*
 * This file is part of Pusher.
 *
 * (c) Jetsung Chan <skiy@jetsung.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pusher\Tests\Channels;

use PHPUnit\Framework\TestCase;
use Pusher\Channel\QQBot;
use Pusher\Message\QQBotMessage;

class QQBotTest extends TestCase
{
    private string $token = '';
    private string $app_id = '';
    private string $channel_id = '';

    private static bool $PASS = false;

    public function setUp(): void
    {
        $token = getenv('QQBotToken');
        if ($token) {
            $this->token = $token;
            $this->app_id = getenv('QQBotAppId');
            $this->channel_id = getenv('QQBotChannelId');
        } else {
            self::$PASS = true;
        }

        // 304022 PUSH_TIME 推送消息时间限制
        // QQ 频道在晚上不可以推送消息
        // 暂时设定为 00:00:00-09:00:00 不测试
        date_default_timezone_set('PRC');
        $day = date('Y-m-d');
        $current_time = time();
        $zero_clock = strtotime($day . ' 00:00:00');
        $nine_clock = strtotime($day . ' 09:00:00');
        if ($zero_clock <= $current_time && $current_time <= $nine_clock) {
            self::$PASS = true;
        }
    }

    public function skipTest(string $func, bool $skip = false): void
    {
        if (self::$PASS || $skip) {
            $this->markTestSkipped("skip ${func}");
        }
    }

    // 延时
    public function timeSleep(int $time = 5): void
    {
        sleep($time);
    }

    public function testContentCases(): void
    {
        $this->skipTest(__METHOD__);
        $this->timeSleep(10);

        $channel = new QQBot();
        $channel->setAppID($this->app_id)
            ->setChannelID($this->channel_id)
            ->Sandbox(false)
            ->setToken($this->token);

        $message = new QQBotMessage('文本类型 content 的消息发送');

        $channel->request($message);
        $this->assertTrue($channel->getStatus());
    }

    public function testImageCases(): void
    {
        $this->skipTest(__METHOD__);
        $this->timeSleep(10);

        $channel = new QQBot();
        $channel->setAppID($this->app_id)
            ->setChannelID($this->channel_id)
            ->Sandbox(true)
            ->setToken($this->token);

        $message = new QQBotMessage();
        $message->setImage('https://www.baidu.com/img/PCtm_d9c8750bed0b3c7d089fa7d55720d6cf.png');

        $channel->request($message);
        $this->assertTrue($channel->getStatus());
    }

    // 不允许发送源文本
    // {"code":50056,"message":"raw markdown not allowed"}
    public function testMarkdownCases(): void
    {
        $this->skipTest(__METHOD__, true);
        $this->timeSleep(10);

        $channel = new QQBot();
        $channel->setAppID($this->app_id)
            ->setChannelID($this->channel_id)
            ->Sandbox(true)
            ->setToken($this->token);

//         $markdown = "![screenshot](https://www.baidu.com/img/PCtm_d9c8750bed0b3c7d089fa7d55720d6cf.png)
        // ### 乔布斯 20 年前想打造的苹果咖啡厅
        // Apple Store 的设计正从原来满满的科技感走向生活化，而其生活化的走向其实可以追溯到 20 年前苹果一个建立咖啡馆的计划";
        $markdown = '## 这是一个 Markdown 内容';

        $message = new QQBotMessage();
        $message->setMarkdown([ 'content' => $markdown]);

        $channel->request($message);
        $this->assertTrue($channel->getStatus());
    }

    public function testEmbedCases(): void
    {
        $this->skipTest(__METHOD__);
        $this->timeSleep(10);

        $channel = new QQBot();
        $channel->setAppID($this->app_id)
            ->setChannelID($this->channel_id)
            ->Sandbox(true)
            ->setToken($this->token);

        $embed = [
            'title' => '这个是标题：Embed',
            'prompt' => '这个是弹窗内容。',
            'thumbnail' => [
                'url' => 'https://www.baidu.com/img/PCtm_d9c8750bed0b3c7d089fa7d55720d6cf.png',
            ],
            'fields' => [
                [ 'name' => '当前等级：黄金' ],
                [ 'name' => '之前等级：白银' ],
                [ 'name' => '😁继续努力' ],
            ],
        ];

        $message = new QQBotMessage();
        $message->setEmbed($embed);

        $channel->request($message);
        $this->assertTrue($channel->getStatus());
    }

    public function testArkCases(): void
    {
        $this->skipTest(__METHOD__);
        $this->timeSleep(10);

        $channel = new QQBot();
        $channel->setAppID($this->app_id)
            ->setChannelID($this->channel_id)
            ->Sandbox(true)
            ->setToken($this->token);

        $ark = [
            'template_id' => 23,
            'kv' => [
                [
                    'key' => '#DESC#',
                    'value' => 'descaaaaaa',
                ],
                [
                    'key' => '#PROMPT#',
                    'value' => 'promptaaaa',
                ],
                [
                    'key' => '#LIST#',
                    'obj' => [
                    [
                        'obj_kv' => [
                        [
                            'key' => 'desc',
                            'value' => '此消息标题：ark 类型',
                        ],
                        ],
                    ],
                    [
                        'obj_kv' =>
                        [
                        [
                            'key' => 'desc',
                            'value' => '当前状态"体验中"点击下列动作直接扭转状态到：',
                        ],
                        ],
                    ],
                    // [
                    //     'obj_kv' => [
                    //     [
                    //         'key' => 'desc',
                    //         'value' => '已评审',
                    //     ],
                    //     [
                    //         'key' => 'link',
                    //         'value' => 'https://qun.qq.com',
                    //     ],
                    //     ],
                    // ],
                    // [
                    //     'obj_kv' => [
                    //     [
                    //         'key' => 'desc',
                    //         'value' => '已排期',
                    //     ],
                    //     [
                    //         'key' => 'link',
                    //         'value' => 'https://qun.qq.com',
                    //     ],
                    //     ],
                    // ],
                    // [
                    //     'obj_kv' => [
                    //     [
                    //         'key' => 'desc',
                    //         'value' => '开发中',
                    //     ],
                    //     [
                    //         'key' => 'link',
                    //         'value' => 'https://qun.qq.com',
                    //     ],
                    //     ],
                    // ],
                    // [
                    //     'obj_kv' => [
                    //     [
                    //         'key' => 'desc',
                    //         'value' => '增量测试中',
                    //     ],
                    //     [
                    //         'key' => 'link',
                    //         'value' => 'https://qun.qq.com',
                    //     ],
                    //     ],
                    // ],
                        [
                            'obj_kv' => [
                            [
                                'key' => 'desc',
                                'value' => '请关注',
                            ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $message = new QQBotMessage();
        $message->setArk($ark);

        $channel->request($message);
        $this->assertTrue($channel->getStatus());
    }

    // 获取用户频道列表 GET /users/@me/guilds
    public function testGuildsCases(): string
    {
        $this->skipTest(__METHOD__, true);

        $channel = new QQBot();
        $channel->setReqURL('/users/@me/guilds')
            ->setAppID($this->app_id)
            ->setMethod('GET')
            ->setToken($this->token);

        $message = new QQBotMessage();
        $channel->request($message);
        $this->assertTrue($channel->getStatus());

        $jsonData = json_decode($channel->getContents(), true);
        if (count($jsonData) > 0) {
            var_dump($jsonData[0]);

            return $jsonData[0]['id'];
        }

        return '';
    }

    /**
     * 获取子频道列表 GET /guilds/{guild_id}/channels
     * @depends testGuildsCases
     */
    public function testChannelsCases(string $guildID): void
    {
        $this->skipTest(__METHOD__, true);

        $this->assertNotEmpty($guildID);

        $channel = new QQBot();
        $channel->setReqURL(sprintf('/guilds/%s/channels', $guildID))
            ->setAppID($this->app_id)
            ->setMethod('GET')
            ->setToken($this->token);

        $message = new QQBotMessage();
        $channel->request($message);

        $this->assertTrue($channel->getStatus());

        $jsonData = json_decode($channel->getContents(), true);
        if (count($jsonData) > 0) {
            echo "\n" . implode(',', array_column($jsonData, 'id'));
        }
    }
}
