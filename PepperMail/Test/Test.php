<?php

require '../PepperMail.php';
/**
 * Test Class
 */
class Test
{
    /**
     * クラスのプライベートメソッドを呼び出す
     *
     * @param object &$object    インスタンスクラス
     * @param string $methodName 機能名前
     * @param array  $parameters 機能のパラメータの配列
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}