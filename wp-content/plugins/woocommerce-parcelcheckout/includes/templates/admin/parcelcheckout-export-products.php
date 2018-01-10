<div class="export-wrapper">
	<h3 class="title">Exporteer producten</h3>
    <p>Exporteer en download de producten als XML formaat. Deze kan gebruikt worden voor het uploaden in de PostNL replenishment.</p>
    <form action="<?php echo admin_url('admin.php?page=postnl-replenishment&action=export'); ?>" method="post">
        <table class="form-table">
            <tr>
                <th>
                    <label for="product_offset">Offset</label>
                </th>
                <td>
                    <input type="text" name="offset" id="product_offset" placeholder="0" class="input-text">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="parcelcheckout_limit">Limit</label>
                </th>
                <td>
                    <input type="text" name="limit" id="parcelcheckout_limit" placeholder="Geen limiet" class="input-text">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_columns">Product kolommen</label>
                </th>
				<table id="datagrid">
					<th style="text-align: left;">
						<label for="v_columns">Kolom</label>
					</th>
					<th style="text-align: left;">
						<label for="v_columns_name">Kolom naam</label>
					</th>
                <?php 
				
                $aPostColumns['images'] = 'Afbeeldingen (featured en gallerij)';
                $aPostColumns['file_paths'] = 'Download paden';
                $aPostColumns['taxonomies'] = 'Categorieën, tags etc';
                $aPostColumns['attributes'] = 'Attributen';
				
               
				foreach ($aPostColumns as $sPostKey => $sPostColumn) 
				{
                    ?>
					<tr>
						<td>
							<input name= "columns[<?php echo $sPostKey; ?>]" type="checkbox" value="<?php echo $sPostKey; ?>" checked>
							<label for="columns[<?php echo $sPostKey; ?>]"><?php echo $sPostColumn ?></label>
						</td>
						<td>
							<?php 
							
							// Load SEO data?
							$sTemporaryKey = $sPostKey;
							
							if(strpos($sPostKey, 'yoast') === false) 
							{
								$sTemporaryKey = ltrim($sPostKey, '_');
							}
							?>
							 <input type="text" name="columns_name[<?php echo $sPostKey; ?>]" value="<?php echo $sTemporaryKey; ?>" class="input-text" />
						</td>
					</tr>
				<?php 
				
				} 
				
				?>
						
					</table>
				</tr>
				<br>
				<tr>
					<th>
						<label for="parcelcheckout_include_hidden_meta"><?php _e('Include hidden meta data', 'wf_csv_import_export'); ?></label>
					</th>
					<td>
						<input type="checkbox" name="include_hidden_meta" id="parcelcheckout_include_hidden_meta" class="checkbox">
					</td>
				</tr>
			</table>
        <p class="submit"><input type="submit" class="button button-primary" value="Exporteer producten" /></p>
	</form>
</div>