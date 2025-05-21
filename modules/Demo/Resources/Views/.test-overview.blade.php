{{--
name: 'test-overview',
base: 0,
active: 0,
override_name: 'Web:web-home',
priority: 0
--}}


{{--<override find="body > p" make="after">--}}
{{--    <p class="demo">Demo</p>--}}
{{--</override>--}}


<override find="p.base" make="attribute-class" remove="base" add="second"></override>
<override find="p.second" make="attribute-value" add="1"></override>
<override find="p.second" make="attribute-value" remove="1" add="2"></override>
