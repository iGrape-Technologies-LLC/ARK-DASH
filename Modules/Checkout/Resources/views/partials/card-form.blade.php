<div class="container" id="card-form">
	<div class="row">
		<div class="col-md-12">
			<div class="form-group mb-3">
				<label class="control-label">Nro. de tarjeta</label>
				<div class="input-group ">
					<div class="input-group-prepend">
	                    <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
	                </div>
					<input type="text" class="form-control credit-card" name="card[card_number]" id="card_number" placeholder="Ingrese nro. de tarjeta" minlength="15" required="required" data-parsley-errors-container="#error_card_number">
				</div>
				<span id="error_card_number"></span>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-4">
			<div class="form-group mb-3">
				<label class="control-label">Mes de vencimiento</label>
				<input type="text" class="form-control" name="card[card_expiration_month]" id="card_expiration_month" placeholder="Ej. 08" minlength="2" required="required">
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-group mb-3">
				<label class="control-label">Año de vencimiento</label>
				<input type="text" class="form-control" name="card[card_expiration_year]" id="card_expiration_year" placeholder="Ej. 20" minlength="2" required="required">
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-group mb-3">
				<label class="control-label">Códico de seguridad</label>
				<input type="text" class="form-control" name="card[security_code]" id="security_code" placeholder="Ej. 123" required="required">
			</div>
		</div>
	</div>
</div>

