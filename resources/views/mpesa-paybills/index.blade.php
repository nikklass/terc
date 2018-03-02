@extends('layouts.master')

@section('title')

    Manage Mpesa Paybills

@endsection

@section('css_header')

    <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css">

@endsection


@section('content')
    
    <div class="container-fluid">
        
		   <!-- Title -->
       <div class="row heading-bg">
          <div class="col-sm-6 col-xs-12">
          <h5 class="txt-dark">
                Manage Mpesa Paybills 
            </h5>
          </div>
          <!-- Breadcrumb -->
          <div class="col-sm-6 col-xs-12">
              {!! Breadcrumbs::render('mpesa-paybills') !!}
          </div>
          <!-- /Breadcrumb -->
       </div>
       <!-- /Title -->

       <!-- Row -->
        <div class="row mt-15">

               
               @include('layouts.partials.error_text')

               
               <div class="col-sm-12 col-xs-12">
                  
                  <div class="panel panel-default card-view panel-refresh">
                     
                     

                       <div class="refresh-container">
                          <div class="la-anim-1"></div>
                       </div>
                       <div class="panel-heading panel-heading-dark">
                          
                          <div class="pull-left col-sm-4">
                                                         
                              <a 
                                href="{{ route('mpesa-paybills.create') }}" 
                                class="btn btn-primary btn-icon right-icon mr-5">
                                <span>New</span>
                                <i class="fa fa-plus"></i>
                              </a>

                              @if (count($mpesapaybills)) 
                                <div class="btn-group">
                                    <div class="dropdown">
                                       <button 
                                          aria-expanded="false" 
                                          data-toggle="dropdown" 
                                          class="btn btn-success dropdown-toggle " 
                                          type="button">
                                          Download 
                                          <span class="caret ml-10"></span>
                                       </button>
                                       <ul role="menu" class="dropdown-menu">
                                          <li><a href="#">As Excel</a></li>
                                          <li><a href="#">As CSV</a></li>
                                          <li><a href="#">As PDF</a></li>
                                       </ul>
                                    </div>
                                </div>
                              @endif

                          </div>

                          <div class="pull-right col-sm-8">
                            
                            @if (count($mpesapaybills)) 

                            <form action="{{ route('mpesa-paybills.index') }}">
                               <table class="table table-search">
                                 <tr>
                                    
                                    <td>
                                      <input type="hidden" value="1" name="search">
                                      
                                      <div class='input-group date' id='start_at_group'>
                                          <input 
                                              type='text' 
                                              class="form-control" 
                                              placeholder="Start Date" 
                                              id='start_at'
                                              name="start_at" 
                                              
                                              @if (app('request')->input('start_at'))
                                                  value="{{ app('request')->input('start_at') }}"
                                              @endif

                                          />
                                          <span class="input-group-addon">
                                             <span class="fa fa-calendar"></span>
                                          </span>
                                       </div>

                                    </td>

                                    <td>
                                      
                                      <div class='input-group date' id='end_at_group'>
                                          <input 
                                              type='text' 
                                              class="form-control" 
                                              placeholder="End Date" 
                                              id='end_at'
                                              name="end_at" 
                                              
                                              @if (app('request')->input('end_at'))
                                                  value="{{ app('request')->input('end_at') }}"
                                              @endif

                                          />
                                          <span class="input-group-addon">
                                             <span class="fa fa-calendar"></span>
                                          </span>
                                       </div>

                                    </td>

                                    <td>
                                      
                                        <a class="btn btn-default btn-icon-anim btn-circle" 
                                        data-toggle="tooltip" data-placement="top"
                                        title="Clear dates" id="clear_date">
                                          <i class="zmdi zmdi-chart-donut"></i>
                                        </a>

                                    </td>
                                    
                                    <td>

                                      <select class="selectpicker form-control" name="user_id" 
                                        data-style="form-control btn-default btn-outline">

                                          <li class="mb-10"><option value="">Select User</option></li>

                                          @foreach ($users as $user) 
                                            <li class="mb-10">
                                              
                                              <option value="{{ $user->id }}"

                                                  @if ($user->id == app('request')->input('user_id'))
                                                      selected="selected"
                                                  @endif

                                                >

                                                {{ $user->first_name }} 
                                                {{ $user->last_name }} - 

                                              </option>

                                            </li>
                                          @endforeach

                                       </select>
                                      
                                    </td>
                                    <td>
                                      <button class="btn btn-primary">Filter</button>
                                    </td>
                                 </tr>
                               </table>
                            </form>

                            @endif
                             
                          </div>
                          <div class="clearfix"></div>

                       </div>


                     @if (!count($mpesapaybills)) 

                         <hr>

                         <div class="panel-heading panel-heading-dark">
                              <div class="alert alert-danger text-center">
                                  No records found
                              </div>
                         </div>

                     @else
                     
                         <div class="panel-wrapper collapse in">
                            <div class="panel-body row pa-0">
                               <div class="table-wrap">
                                  <div class="table-responsive">
                                     <table class="table table-hover mb-0">
                                        <thead>
                                           <tr>
                                              <th width="5%">id</th>
                                              <th width="10%">Paybill No.</th>
                                              <th width="10%">Till No.</th>
                                              <th width="10%">Paybill Type</th>
                                              <th width="15%">Name</th>
                                              <th width="15%">Company</th>
                                              <th width="15%">Created At</th>
                                              <th width="20%">Actions</th>
                                           </tr>
                                        </thead>
                                        <tbody>

                                           @foreach ($mpesapaybills as $mpesapaybill)                                  
                                             <tr>

                                                <td>
                                                  <span class="txt-dark weight-500">
                                                    {{ $mpesapaybill->id }}
                                                  </span>
                                                </td>

                                                <td>
                                                  <span class="txt-dark weight-500">
                                                    {{ $mpesapaybill->paybill_number }}
                                                  </span>
                                                </td>

                                                <td>
                                                  <span class="txt-dark weight-500">
                                                    {{ $mpesapaybill->till_number }}
                                                  </span>
                                                </td>

                                                <td>
                                                  <span class="txt-dark weight-500">
                                                    {{ $mpesapaybill->type }}
                                                  </span>
                                                </td>

                                                <td>
                                                  <span class="txt-dark weight-500">
                                                    {{ $mpesapaybill->name }}
                                                  </span>
                                                </td>

                                                <td>
                                                  <span class="txt-dark weight-500">
                                                    @if ($mpesapaybill->company)
                                                      {{ $mpesapaybill->company->name }}
                                                    @endif
                                                  </span>
                                                </td>

                                                <td>
                                                   <span class="txt-dark weight-500">
                                                   {{ formatFriendlyDate($mpesapaybill->created_at) }}
                                                   </span>
                                                </td>
                                                
                                                <td>

                                                   <a href="{{ route('mpesa-paybills.show', $mpesapaybill->id) }}" class="btn btn-info btn-sm btn-icon-anim btn-square">
                                                    <i class="zmdi zmdi-eye"></i> 
                                                   </a>

                                                   @if (Auth::user()->can('update-paybill'))
                                                     <a href="{{ route('mpesa-paybills.edit', $mpesapaybill->id) }}" class="btn btn-primary btn-sm btn-icon-anim btn-square">
                                                        <i class="zmdi zmdi-edit"></i> 
                                                     </a>
                                                   @endif

                                                   @if (Auth::user()->can('delete-paybill'))
                                                     <a href="{{ route('mpesa-paybills.destroy', $mpesapaybill->id) }}" class="btn btn-danger btn-sm btn-icon-anim btn-square">
                                                        <i class="zmdi zmdi-delete"></i> 
                                                     </a>
                                                   @endif

                                                </td>
                                             </tr>
                                           @endforeach 
                                           
                                        </tbody>
                                     </table>
                                  </div>
                               </div>
                               <hr>
                               <div class="text-center">
                                   {{ $mpesapaybills->links() }}
                               </div>   
                            </div>   
                         </div>

                     @endif

                  </div>

               </div>

        </div>   
        <!-- Row -->

    </div>
         

@endsection


@section('page_scripts')

  <script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
  <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>

  <script type="text/javascript">
      /* Start Datetimepicker Init*/
      $('#start_at_group').datetimepicker({
          useCurrent: false,
          format: 'DD-MM-YYYY',
          icons: {
                        time: "fa fa-clock-o",
                        date: "fa fa-calendar",
                        up: "fa fa-arrow-up",
                        down: "fa fa-arrow-down"
                    },
        }).on('dp.show', function() {
        if($(this).data("DateTimePicker").date() === null)
          $(this).data("DateTimePicker").date(moment());
      });

      /* End Datetimepicker Init*/
      $('#end_at_group').datetimepicker({
          useCurrent: false,
          format: 'DD-MM-YYYY',
          icons: {
                        time: "fa fa-clock-o",
                        date: "fa fa-calendar",
                        up: "fa fa-arrow-up",
                        down: "fa fa-arrow-down"
                    },
        }).on('dp.show', function() {
        if($(this).data("DateTimePicker").date() === null)
          $(this).data("DateTimePicker").date(moment());
      });

      //clear date
      $("#clear_date").click(function(e){
          e.preventDefault();
          $('#start_at').val("");
          $('#end_at').val("");
      });

  </script>

@endsection

