{{--
name: 'extend_sidebar_layout',
base: 0,
active: 1,
override_name: 'Web:layout-sidebar',
priority: 0
--}}

<override find="body.min-h-screen" make="before">
    <p>Test before</p>
</override>

<override find="body.min-h-screen" make="inside">
    <p>Test before</p>
</override>

<override find="body.min-h-screen" make="attribute-class" add="new"></override>

<override find="flux:sidebar.toggle" make="attribute-class" add="demo"></override>

{{--<override find="flux:sidebar.toggle" make="attribute-icon" remove="x-mark" add="demo"></override>--}}
