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