# WpTemplateHelper

Use it to wrap data and get escaped output automatically checking for existence.

Simple example:

```php
<?php
$data = [
'foo' => 'bar',
'foo_url' => 'https://example.com',
'style' => 'color: red; font-weight: bold',
'img' => 'https://picsum.photos/100/100'
];

$t = new \Lxbdr\WpTemplateHelper\WpTemplateHelper($data);

?>

<div class="card">
    <?php
        if ($t->notEmpty('img')): ?>
            <img src="<?php $t->url('img'); ?>" alt="Beautiful picture">
        <?php
        endif; ?>
    <h1 class="card-title"><?php $t('foo'); ?></h1>
    <p style="<?php $t->attr('style'); ?>">lorem ipsum</p>
    <a href="<?php $t->url('foo_url'); ?>">Read more</a> 
</div>

<?php
// ...
```
