{include file="findInclude:common/templates/header.tpl"}
{block name="stations"}
<h1>{$page.title}</h1>
{include file="findInclude:common/templates/results.tpl" results=$itemList}
{/block}
{include file="findInclude:common/templates/footer.tpl"}
