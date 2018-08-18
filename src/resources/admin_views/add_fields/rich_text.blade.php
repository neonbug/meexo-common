<tr class="top aligned field-rich-text">
	<th class="collapsing">
		{{ $field_title }}
	</th>
	<td>
		<div class="field">
			<textarea
				name="field[{{ $id_language }}][{{ $field['name'] }}]" 
				data-name="{{ $field['name'] }}"
				data-type="rich_text"
				data-extra-plugins="{{ array_key_exists('extra_plugins', $field) ? json_encode($field['extra_plugins']) : '' }}"
				>{{ $field['value'] }}</textarea>
		</div>
	</td>
</tr>
