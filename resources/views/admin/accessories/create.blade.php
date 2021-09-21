@extends('admin.layouts.master')

@section('title') Customer Add @endsection

@section('content')
   @component('admin.common-components.breadcrumb')
         @slot('title') Customer Add  @endslot
         @slot('li_1')  @endslot
         @slot('li_2')@endslot
     @endcomponent

                        <div class="row">
                              @if(Session::has('message'))
                              <div class="col-12">
                                  {!!Session::get('message')!!}
                              </div>
                              @endif
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form action="{{route('admin.accessory.store')}}" method="post" enctype="multipart/form-data">
                                            {{csrf_field()}}

                                            <div class="form-group row">
                                                <label for="example-text-input" class="col-md-2 col-form-label">Brand</label>
                                                <div class="col-md-10">
                                                <select name="brand_id"  class="form-control selectpic" onchange="getModel(this)">
                                                    <option>select anyone</option>
                                                    @foreach (CityClass::brands() as $brand)
                                                    <option value="{{$brand->id}}">{{$brand->brand_name}}</option>
                                                    @endforeach
                                                </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="example-text-input" class="col-md-2 col-form-label">Model</label>
                                                <div class="col-md-10" id="showModels">

                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="example-email-input" class="col-md-2 col-form-label">Category</label>
                                                <div class="col-md-10">
                                                    <select name="category"  class="form-control selectpic">
                                                       <option>select anyone</option>
                                                       <option value="charger">Charger</option>
                                                       <option value="protector">Screen Protector</option>
                                                       <option value="cables">Cable</option>
                                                       <option value="battery">Battery</option>
                                                   </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="example-text-input" class="col-md-2 col-form-label">Name</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" name="name" type="text" placeholder="Enter name" @if(old('name')) value="{{ old('name') }}" @endif  name="name" id="example-text-input">
                                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="example-search-input" class="col-md-2 col-form-label">Sell Price</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="number" placeholder="Enter Sell Price" @if(old('sell_price')) value="{{ old('sell_price') }}" @endif name="sell_price" id="example-search-input">
                                                    <span class="text-danger">{{ $errors->first('sell_price') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="example-search-input" class="col-md-2 col-form-label"> Original Price</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="number" placeholder="Enter orig_price" @if(old('orig_price')) value="{{ old('orig_price') }}" @endif name="orig_price" id="example-search-input">
                                                    <span class="text-danger">{{ $errors->first('orig_price') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="example-search-input" class="col-md-2 col-form-label"> Quantity</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="number" placeholder="Enter quantity" @if(old('quantity')) value="{{ old('quantity') }}" @endif name="quantity" id="example-search-input">
                                                    <span class="text-danger">{{ $errors->first('quantity') }}</span>
                                                </div>
                                            </div>
                                             <div class="form-group row">
                                                <label for="example-tel-input" class="col-md-2 col-form-label">Description</label>
                                                <div class="col-md-10">
                                                    <textarea type="text" name="description" class="form-control" cols="30" rows="10"></textarea>
                                                    <span class="text-danger">{{ $errors->first('description') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="example-search-input" class="col-md-2 col-form-label"> Original Price</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="file"  name="images[]" multiple id="example-search-input">
                                                    <span class="text-danger">{{ $errors->first('images') }}</span>
                                                </div>
                                            </div>

                                        <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
                                    </div>

                                   </form>

                                    </div>
                                </div>
                            </div> <!-- end col -->

                        </div>
                        <!-- end row -->

                        <!-- end row -->
@endsection

@section('script')

<script>
    $(function() {
$('.selectpic').select2();
});

</script>

<script>
    function getModel(event)
    {
        var id = $(event).val();
        $.ajax({
        url: "{{url('admin/accessory/getModels')}}/"+id,
        type:"get",
        success:function(response){
          console.log(response);
          $('#showModels').html(response);
        },

       });
    }
</script>
@endsection