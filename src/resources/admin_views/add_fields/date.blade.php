<tr class="top aligned field-date">
	<th class="collapsing">
		{{ $field_title }}
		@if (array_key_exists('required', $field) && $field['required'] === true)
			<i class="orange small asterisk icon" 
				title="{{ trans('common::admin.add.errors.validation.required') }}"></i>
		@endif
	</th>
	<td>
		<div class="field">
			<input type="text" value="{{ $field['value'] === null || $field['value'] == '' ? '' : $formatter->formatShortDate(strtotime($field['value'])) }}" 
				data-name="{{ $field['name'] }}" data-type="date" 
				data-date-rel="field[{{ $id_language }}][{{ $field['name'] }}]" />
			<input type="hidden" name="field[{{ $id_language }}][{{ $field['name'] }}]" 
				value="{{ $field['value'] === null || $field['value'] == '' ? '' : date('Y-m-d', strtotime($field['value'])) }}" />
		</div>
	</td>
</tr>
