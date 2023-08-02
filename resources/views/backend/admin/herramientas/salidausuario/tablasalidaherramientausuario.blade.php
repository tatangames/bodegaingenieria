@foreach($dataArray as $dd)

    @if($hayCantidad)

        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <div class="form-group">

                        <div class="row">

                            <div class="col-sm-3 border-right">
                                <div class="description-block">
                                    <h4 class="description-header">{{ $dd['cantidadtotal'] }}</h4>
                                    <span class="description-text">Disponible</span>
                                </div>
                            </div>

                            <div class="col-sm-7 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $dd['nombre'] }}</h5>
                                    <span class="description-text">Herramienta</span>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="arraycolor">
                    <div class="card-body row">
                        <input name="arraysalida[]" type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" data-idcantidad="{{$dd['id']}}"  data-maxcantidad="{{$dd['cantidadtotal']}}" value="0" min="0" max="{{ $dd['cantidadtotal'] }}" step="1"/>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endforeach

@if($hayCantidad)
    <br>
    <br>
    <!-- partida de mano de obra -->
    <div class="modal-footer justify-content-between" style="float: right !important;">
        <button type="button" class="btn btn-success" onclick="verificarSalida()">Verificar</button>
    </div>
@endif
<script type="text/javascript">
    $(document).ready(function(){
        $("input[type='number']").inputSpinner({

            decrementButton: "<strong>-</strong>",
            incrementButton: "<strong>+</strong>",
        });

        let haycantidad = {!! json_encode($hayCantidad) !!};

        if(!haycantidad){
            toastr.info("Sin inventario");
        }
    });

</script>
</body>
</html>

