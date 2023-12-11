<?php
/**
 * @package     System - WT Eternal admin
 * @version     1.0.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2023 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wteternaladmin\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

final class Wteternaladmin extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;
    protected $allowLegacyListeners = false;

    /**
     *
     * @return array
     *
     * @throws \Exception
     * @since 4.1.0
     *
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterDispatch' => 'onAfterDispatch'
        ];
    }

    /**
     * Добавляем js-скрпиты на HTML-фронт
     *
     * @throws \Exception
     * @since 1.0.0
     * @see   https://habr.com/ru/articles/672020/
     * @see   https://habr.com/ru/post/677262/
     */
    function onAfterDispatch(): void
    {
        // We are not work in Joomla API or CLI ar Admin area
        if (!$this->getApplication()->isClient('administrator')) return;

        $doc = $this->getApplication()->getDocument();
        $wa = $doc->getWebAssetManager();
        $lifetime = intval(Factory::getContainer()->get('config')->get('lifetime'));
        $timeout = $lifetime * 60 / 3 * 1000;
        $js = <<<EOM
            
            (function() {
                document.addEventListener('DOMContentLoaded', () => {
                        window.refreshSession = function () {
                        
                            Joomla.request({   
                              url: Joomla.getOptions('system.paths').baseFull,
                              onSuccess: function (response, xhr){
                                console.log('%cYour session successfully updated', 'background-color:green;padding:7px;color:#fff;font-size:0.9rem');
                               },
                              onError: function(xhr){
                                console.log('%cYour session wasn\'t updated', 'background-color:red;padding:7px;color:#fff;font-size:0.9rem');
                              }
                            })
                        };

                    setInterval("window.refreshSession()", $timeout);
                });
               })();
               
            EOM;

        $wa->addInlineScript($js, ['name' => 'inline.wt_eternal_admin']);

    }
}