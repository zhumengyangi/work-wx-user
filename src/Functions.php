<?php

declare(strict_types=1);
/**
 * This file is part of KnowYourself.
 *
 * @link     https://www.zhiwotansuo.com
 * @document https://github.com/kydever/work-wx-user/blob/main/README.md
 * @contact  l@hyperf.io
 * @license  https://github.com/kydever/work-wx-user/blob/main/LICENSE
 */
namespace KY\WorkWxUser;

use Hyperf\Utils\ApplicationContext;

function di(?string $id = null)
{
    $container = ApplicationContext::getContainer();
    if ($id) {
        return $container->get($id);
    }

    return $container;
}
