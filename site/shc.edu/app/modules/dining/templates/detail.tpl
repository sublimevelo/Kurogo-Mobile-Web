{include file="findInclude:common/templates/header.tpl"}
{block name="item"}
<h1>Nutrition Information for {{$item.title}}</h1>
<div class="focal">
{foreach $item.content as $key=>$val}
<p>{$key}: {$val}</p>
{/foreach}
</div>
{/block}
{include file="findInclude:common/templates/footer.tpl"}
