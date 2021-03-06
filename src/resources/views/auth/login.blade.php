<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ trans('common::admin.login.title') }}</title>
	
	<script src="{{ cached_asset('vendor/common/admin_assets/js/jquery-2.1.4.min.js') }}"></script>
	<script src="{{ cached_asset('vendor/common/admin_assets/js/semanticui/semantic.min.js') }}"></script>
	<script src="{{ cached_asset('vendor/common/admin_assets/js/app/login.js') }}"></script>
	
	<link rel="stylesheet" type="text/css" 
		href="{{ cached_asset('vendor/common/admin_assets/js/semanticui/semantic.min.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ cached_asset('vendor/common/admin_assets/css/login.css') }}" />
	
	<script type="text/javascript">
	login.init();
	</script>
	
	<?php
	$background_idx = rand(1, 11);
	$background_url = file_get_contents('vendor/common/admin_assets/images/login/backgrounds/' . 
		$background_idx . '.txt');
	?>
</head>
<body class="background-{{ $background_idx }}">

<div class="ui middle aligned center aligned grid">
	<div class="column">
		<div>
			<form class="ui large form" method="POST">
				<div class="ui orange segment inverted top attached">
					<h3>{{ trans('common::admin.login.title') }}</h3>
				</div>
				<div class="ui orange padded segment bottom attached">
					<input type="hidden" name="_token" value="{{ csrf_token() }}" />

					<div class="field {{ $errors->has('username') ? 'error' : '' }}">
						<div class="ui left icon input">
							<i class="user icon"></i>
							<input type="text" name="username" placeholder="{{ trans('common::admin.login.username') }}" 
								value="{{ old('username') }}" autofocus />
						</div>
					</div>

					<div class="field {{ $errors->has('password') ? 'error' : '' }}">
						<div class="ui left icon input">
							<i class="lock icon"></i>
							<input type="password" name="password" placeholder="{{ trans('common::admin.login.password') }}">
						</div>
					</div>

					<div>
						<button type="submit" class="ui button orange">
							<i class="power icon"></i>
							{{ trans('common::admin.login.login-button') }}
						</button>
					</div>
				</div>
			</form>
			
			@if (count($errors) > 0)
				<div class="ui error icon message">
					<i class="frown icon"></i>
					<div class="content">
						@foreach ($errors->all() as $error)
							<p>{{ $error }}</p>
						@endforeach
					</div>
				</div>
			@endif
		</div>
	</div>
</div>
<a class="background-image-attribution" href="{{ $background_url }}" target="_blank">
	{{ $background_url }}
</a>

</body>
</html>
