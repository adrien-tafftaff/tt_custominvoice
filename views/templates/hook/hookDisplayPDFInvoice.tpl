{*
 * @category    Module / customizations
 * @author      Adrien THIERRY www.tafftaff.fr
 * @copyright   2021 Adrien THIERRY
 * @version     1.0
 * @link        https://www.tafftaff.fr
 * @since       File available since Release 1.0
*}
<table>
<tr>
	<td  height="10">&nbsp;</td>
</tr>

<tr>
	<td  class="center">
		<table id="comment-to-show" style="width: 100%">
			<tr>
				<td class="grey">{$title}</td>
			</tr>
			<tr>
				<td class="note">{$text}</td>
			</tr>
		</table>
	</td>
</tr>

</table>
<table width="100%" cellpadding="4" cellspacing="0">
	<thead>
		<tr>
			<th class="product header small">{l s='Produit' d='Shop.Pdf' pdf='true'}</th>
			<th class="product header small center">{l s='Réference' d='Shop.Pdf' pdf='true'}</th>
			<th class="product header small center">{l s='Code douane' d='Shop.Pdf' pdf='true'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach $productsdetails as $product}
		<tr class="product">
			<td>{$product.product_name}</td>
			<td class="center">{$product.product_reference}</td>
			<td class="center">{$product.hscode}</td>
		</tr>
		{/foreach}
	</tbody>
</table>