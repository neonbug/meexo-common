<tr class="top aligned field-multi-line-text">
	<th class="collapsing">
		{{ $field_title }}
	</th>
	<td>
		<div class="field">
			<textarea name="field[{{ $id_language }}][{{ $field['name'] }}]" 
				data-name="{{ $field['name'] }}" data-type="multi_line_text">{{ $field['value'] }}</textarea>
			
			@if (array_key_exists('note', $field) && $field['note'] != '')
				<div><div class="ui pointing label">{!! trans($field['note']) !!}</div></div>
			@endif
		</div>
	</td>
</tr>
