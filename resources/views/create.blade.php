@extends('layout')

<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>

    // datepicker
    $( function() {
        $( "#dateInput" ).datepicker();
    });

      
    // block certain keys on rutInput
    (function($) {
        $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
            if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
            } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
            } else {
                this.value = "";
            }
            });
        };
    }(jQuery));


    // only numbers, k and K can be typed in input
    $(document).ready(function() {
        $("#rutInput").inputFilter(function(value) {
            return /^((\d*)|((\d*)(k|K){1}))$/.test(value);  
        });
    });


    // validate Rut
    $(function(){
        $('#rutInput').on('change', function(){
                    
            var rut = $(this).val();
            var errorSpan = document.getElementById('rutError');

            if (rut.length == 1) {
                errorSpan.innerHTML = 'Rut inválido';
                document.getElementById('submitButton').disabled = true;
                return false;
            }

            // check if rut is valid
            verif = rut.slice(-1);
            body = rut.substring(0, rut.length - 1);
            long = body.length;
            

            if (long == 0) {
                errorSpan.innerHTML = '';
            } else if (long < 7) {
                errorSpan.innerHTML = 'Rut inválido';
                document.getElementById('submitButton').disabled = true;
                return false;
            } else {
                errorSpan.innerHTML = '';
                document.getElementById('submitButton').disabled = false;
            } 

            if (verif == 'K') {
                verif = 'k';
            }

            // calculate verif number
            var i = 0;
            var aux = 2;
            var sum = 0;
            var last = long - 1;
            while (i < long) {
                if (aux == 8){
                    aux = 2;
                }
                sum = sum + (body[last] * aux);
                aux++;
                last--;
                i++;
            }
            mod = (Math.trunc(sum / 11)) * 11;
            rest = sum - mod;
            final = 11 - rest;
            if (final == 10) {
                final = 'k';
            } else if ( final == 11) { 
                final = 0;
            }

            // compare final with verif
            if (final == verif) {
                errorSpan.innerHTML = '';
                document.getElementById('submitButton').disabled = false;
            } else {
                errorSpan.innerHTML = 'Rut inválido';
                document.getElementById('submitButton').disabled = true;
                return false;
            }


            // validate if ruts already registered
            $.ajax({
                type: 'post',
                url: "{{ route('ajaxRut') }}",
                data: {
                    rut: rut,
                    "_token": $("meta[name='csrf-token']").attr("content")
                },
                success: function(data){
                    if (data){
                        $('#rutError').html('Este Rut ya se encuentra en uso');
                        document.getElementById('submitButton').disabled = true;
                        return false;
                    }
                    else{
                        document.getElementById('submitButton').disabled = false;
                    }
                }
            });
        });
    });
    

    // validate email
    $(function(){
        $('#emailInput').on('change', function(){
            
            var email = $(this).val();
            var emailErrorSpan = document.getElementById('emailError');
            

            if (email.length == 0){
                emailErrorSpan.innerHTML = '';
                return false;
            }

            // validate email
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
                document.getElementById('submitButton').disabled = false;
            } else {
                $('#emailError').html('Email no válido');
                document.getElementById('submitButton').disabled = true;
                return false;
            }

            // validate if ruts already registered
            $.ajax({
                type: 'post',
                url: "{{ route('ajaxEmail') }}",
                data: {
                    email: email,
                    "_token": $("meta[name='csrf-token']").attr("content")
                },
                success: function(data){
                    if (data){
                        $('#emailError').html('Este Email ya se encuentra en uso');
                        document.getElementById('submitButton').disabled = true;
                        return false;
                    }
                    else{
                        $('#emailError').html('');
                        document.getElementById('submitButton').disabled = false;
                    }
                }
            });
        });
    });



    // compare passwords
    $(function(){
        $('#repeatPasswordInput').on('change', function(){
            
            var repeatedPassword = $(this).val();
            var password = document.getElementById('passwordInput').value;
            var passwordErrorSpan = document.getElementById('passwordError');
            
            if (repeatedPassword.length == 0){
                passwordErrorSpan.innerHTML = '';
            } else if (password != repeatedPassword){
                passwordErrorSpan.innerHTML = 'Contraseñas no coinciden';
                document.getElementById('submitButton').disabled = true;
                return false;
            } else {
                passwordErrorSpan.innerHTML = '';
                document.getElementById('submitButton').disabled = false;
            }
        });
    });

</script>


@section('content')
    <h1>Crear Usuario</h1> 
    <form action="/store" method="POST" role="form">
        @csrf
        <div class="form-group">
            <label for="nameInput">Nombres</label>
            <input required type="text" class="form-control" id="nameInput"  placeholder="Ingrese sus nombres" name="nameInput">
        </div>
        <div class="form-group">
            <label for="lastNameInput">Apellidos</label>
            <input required type="text" class="form-control" id="lastNameInput"  placeholder="Ingrese sus apellidos" name="lastNameInput">
        </div>
        <div style="display: inline-block; width:45%; margin: 0px 0px 16px">
            <label for="rutInput">Rut</label>
            <input required type="text" class="form-control" id="rutInput"  placeholder="Ingrese su Rut" name="rutInput">
            <span class="badge badge-light" id="rutError"></span>
        </div>
        
        <div style="display: inline-block; float: right; width:45%; margin: 0px 0px 16px">
            <label for="dateInput">Fecha de nacimiento</label>
            <input required type="text" class="form-control" id="dateInput" placeholder="Seleccione su fecha de nacimiento" name="dateInput" onkeypress="validar(event)">
        </div>
        <div class="form-group">
            <label for="emailInput">Email</label>
            <input required type="email" class="form-control" id="emailInput" placeholder="Ingrese su Email" name="emailInput">
            <span class="badge badge-light" id="emailError"></span>
        </div>
        <div class="form-group">
            <label for="passwordInput">Contraseña</label>
            <input required type="password" class="form-control" id="passwordInput" placeholder="Ingrese su Contraseña" name="passwordInput">
        </div>
        <div class="form-group">
            <label for="repeatPasswordInput">Repita su Contraseña</label>
            <input required type="password" class="form-control" id="repeatPasswordInput" placeholder="Repita su Contraseña" name="repeatPasswordInput">
            <span class="badge badge-light" id="passwordError"></span>
        </div>
        
        <button type="submit" class="btn btn-primary" id="submitButton">Submit</button>
    </form>
@endsection