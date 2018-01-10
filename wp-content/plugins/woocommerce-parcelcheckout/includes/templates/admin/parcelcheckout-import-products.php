
<div class="import-wrapper">
	<h3 class="title">Importeer producten</h3>
	<p>Importeer producten ( simple products ) in XML formaat vanuit de PostNL replenishment</p>
	<p class="submit"><?php
		$sImportUrlWithMerge = admin_url('admin.php?import=parcelcheckout-import&merge=1');
		$sImportUrl = admin_url('admin.php?import=parcelcheckout-import');

		?>
		<a class="button button-primary" id="parcelcheckout-url" href="<?php echo admin_url('admin.php?import=parcelcheckout-import'); ?>">Importeer producten</a>
		<input type="checkbox" id="merge" value="0">Merge/Overschrijf bestaande producten <br>
	</p>
</div>
<script type="text/javascript">
	jQuery('#merge').click(function () 
	{
		if (this.checked) 
		{
			jQuery("#parcelcheckout-url").attr("href", '<?php echo $sImportUrlWithMerge ?>');
		} 
		else 
		{
			jQuery("#parcelcheckout-url").attr("href", '<?php echo $sImportUrl ?>');
		}
	});
</script>