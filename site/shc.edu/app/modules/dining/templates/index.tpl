{include file="findInclude:common/templates/header.tpl"}
{block name="meals"}
{ if $mealList }
{include file="findInclude:common/templates/results.tpl" results=$mealList}
{ else }
<p>Dining schedule is not currently available.</p>
{ /if }
{/block}
<div class="focal smallprint"><p>Your favorites from the grill, deli, or salad bar are always there for you too.</p></div>
{include file="findInclude:common/templates/footer.tpl"}
