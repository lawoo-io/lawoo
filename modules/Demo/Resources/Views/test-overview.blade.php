{{--
name: 'test-overview',
base: 0,
active: 1,
override_name: 'Web:index',
priority: 0
--}}

<override find="div.flex" make="after">
    <p class="demo">Demo after</p>
</override>

<override find="x-web.layouts.sidebar" make="before">
    <p class="demo">Demo before</p>
</override>

<override find="div.flex" make="attribute-class" add="second"></override>
<override find="div.second" make="attribute-value" add="1"></override>
<override find="div.second" make="attribute-value" remove="1" add="2"></override>
