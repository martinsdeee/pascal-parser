<?php

namespace spec\App;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParseSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('App\Parse');
    }

    function its_set_code_property()
    {
        $this->create('code');
        $this->getCode()->shouldReturn('code');
    }

    function its_make_raw_code()
    {
        $this->create('k           :=           0;');
        $this->raw()->shouldReturn('k := 0 ; ');
    }

    function its_check_type_of_code_chunk()
    {
        $this->checkType('procedure')->shouldReturn('keyword');
        $this->checkType('.')->shouldReturn('splitter');
        $this->checkType("'")->shouldReturn('splitter');
    }

    function its_get_element_id()
    {
        $variables = [
            "k",
            "l"
        ];
        $this->getElementId($variables, 'k')->shouldReturn(0);
        $this->getElementId($variables, 'l')->shouldReturn(1);
        $this->getElementId($variables, 'x')->shouldReturn(null);
    }



}
