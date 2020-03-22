<?php
/**
 * Created by PhpStorm.
 * User: kro
 * Date: 22.03.20
 * Time: 17:21
 */

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Templater\Service\Template;

class TemplateTest extends TestCase
{
    public function testRender()
    {
        $t = new Template();
        $st = $t->render('views/start.html', [
            'my_var' => 'test7897897',
            'bool_var' => false
        ]);
    }
}