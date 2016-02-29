<?php


namespace Dimple;


use Prophecy\Argument;
use Prophecy\Prophet;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Prophet */
    private $prophet;
    /** @var Container */
    private $container;

    public function testOffsetGet_attemptsToLoadServiceProvider()
    {
        $isCalledSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::exact($this->container))
            ->shouldBeCalled()
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['test::something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());


        $this->assertEquals('somethingElse', $this->container['test::something']);
        $this->assertTrue($isCalledSpy);
    }

    public function testOffsetExists_attemptsToLoadServiceProvider()
    {
        $isCalledSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::exact($this->container))
            ->shouldBeCalled()
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['test::something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());


        $this->assertTrue(isset($this->container['test::something']));
        $this->assertFalse($isCalledSpy);
    }

    public function testOffsetGet_lazyLoadedServiceProvider()
    {
        $isCalledSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::exact($this->container))
            ->shouldNotBeCalled()
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['test::something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());


        $this->assertFalse($isCalledSpy);
    }

    public function testOffsetGet_serviceProviderRegisteredOnlyOnce()
    {
        $isCalledSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::exact($this->container))
            ->shouldBeCalledTimes(1)
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['test::something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());


        $this->assertEquals('somethingElse', $this->container['test::something']);
        $this->assertEquals('somethingElse', $this->container['test::something']);
        $this->assertTrue($isCalledSpy);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterServiceProviderProvider_CantRedefineNameSpace()
    {
        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());
        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterServiceProviderProvider_NameSpaceMustBeString()
    {
        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                false => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterServiceProviderProvider_provideServiceProvidersMustProvideServiceProviders()
    {
        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => function () {}
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetGet_unknownNamespace_throwsException()
    {
        $this->container['test::something'];
    }

    public function testOffsetGet_noNamespace_StillWorks()
    {
        $this->container['bla'] = 'test';
        $this->assertEquals('test', $this->container['bla']);
    }

    public function testRaw_attemptsToLoadServiceProvider()
    {
        $isCalledSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::exact($this->container))
            ->shouldBeCalled()
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['test::something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());


        /** @var callable $callable */
        $callable = $this->container->raw('test::something');
        $this->assertThat($callable, $this->isType('callable'));
        $this->assertFalse($isCalledSpy);
        $output = $callable();
        $this->assertTrue($isCalledSpy);
        $this->assertEquals($output, 'somethingElse');
    }

    public function testExtend_attemptsToLoadServiceProvider()
    {
        $isCalledSpy = false;
        $isCalledOtherSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::exact($this->container))
            ->shouldBeCalled()
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['test::something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());


        $this->container->extend('test::something', function ($instance) use (&$isCalledOtherSpy) {
            $isCalledOtherSpy = true;
            return $instance . 'Entirely';
        });


        $this->assertFalse($isCalledSpy);
        $this->assertFalse($isCalledOtherSpy);

        $this->assertEquals('somethingElseEntirely', $this->container['test::something']);
        $this->assertTrue($isCalledSpy);
        $this->assertTrue($isCalledOtherSpy);
    }

    public function testOffsetUnset_stillWorks()
    {
        $this->container['test'] = true;
        $this->assertTrue(isset($this->container['test']));


        unset($this->container['test']);
        $this->assertFalse(isset($this->container['test']));
    }

    public function testFactory_StillMarksAsFactory()
    {
        $called = 0;
        $this->container['test'] = $this->container->factory(function () use (&$called) {
            $called += 1;
            return true;
        });


        $this->container['test'];
        $this->container['test'];


        $this->assertEquals(2, $called);
    }

    public function testProtect_stillProtectsFromExecution()
    {
        $called = 0;
        $anonymousFunction = function () use (&$called) {
            $called += 1;
            return true;
        };

        $this->container['test'] = $this->container->protect($anonymousFunction);


        $actual = $this->container['test'];


        $this->assertEquals($anonymousFunction, $actual);
        $this->assertEquals(0, $called);
    }

    public function testKeys_onlyIncludeKeysAfterLoading()
    {
        $isCalledSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::exact($this->container))
            ->shouldBeCalled()
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['test::something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });

        $serviceProviderProviderProphecy = $this->prophet->prophesize('\Dimple\ServiceProviderProviderInterface');
        $serviceProviderProviderProphecy
            ->provideServiceProviders(Argument::exact($this->container))
            ->shouldBeCalled()
            ->willReturn(array(
                'test' => $serviceProviderProphecy->reveal()
            ));


        $this->container->registerServiceProviderProvider($serviceProviderProviderProphecy->reveal());


        $this->assertNotContains('test::something', $this->container->keys());
        $this->assertTrue(isset($this->container['test::something']));


        $this->assertContains('test::something', $this->container->keys());
    }

    public function testRegister_stillRegisters()
    {

        $isCalledSpy = false;

        $serviceProviderProphecy = $this->prophet->prophesize('\Pimple\ServiceProviderInterface');
        $serviceProviderProphecy
            ->register(Argument::type('\Pimple\Container'))
            ->shouldBeCalled()
            ->will(function ($args) use (&$isCalledSpy) {
                $args[0]['something'] = function () use (&$isCalledSpy) {
                    $isCalledSpy = true;
                    return 'somethingElse';
                };
            });


        $this->container->register($serviceProviderProphecy->reveal());


        $this->assertEquals('somethingElse', $this->container['something']);
        $this->assertTrue($isCalledSpy);
    }

    protected function setup()
    {
        $this->prophet = new Prophet;
        $this->container = new Container;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }
}
