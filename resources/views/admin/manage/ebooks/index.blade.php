@extends('admin.layouts.master')

@section('title')

    Manage Ebooks

@endsection

@section('css_header')

    <link href="{{ asset('admin/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('admin/css/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css">

@endsection


@section('content')
    
    <div class="container-fluid">
        
		   <!-- Title -->
       <div class="row heading-bg">
          <div class="col-sm-6 col-xs-12">
          <h5 class="txt-dark">
                Manage Ebooks
            </h5>
          </div>
          <!-- Breadcrumb -->
          <div class="col-sm-6 col-xs-12">
              {!! Breadcrumbs::render('ebooks') !!}
          </div>
          <!-- /Breadcrumb -->
       </div>
       <!-- /Title -->

       <!-- Row -->
        <div class="row mt-15">

               
               @include('admin.layouts.partials.error_text')

               
               <div class="col-sm-12 col-xs-12">
                  
                  <div class="panel panel-default card-view panel-refresh">
                     
                       <div class="refresh-container">
                          <div class="la-anim-1"></div>
                       </div>
                       <div class="panel-heading panel-heading-dark">
                          
                          <div class="pull-left col-sm-3">

                            

                          </div>

                          <div class="pull-right col-sm-9">
                            
                            <form action="{{ route('ebooks.index') }}">
                               <table class="table table-search">
                                 <tr>
                                    
                                    <td>
                                      <input type="hidden" value="1" name="search">
                                      
                                      <div class='input-group date' id='start_date_group'>
                                          <input 
                                              type='text' 
                                              class="form-control" 
                                              placeholder="Start Date" 
                                              id='start_date'
                                              name="start_date" 
                                              
                                              @if (app('request')->input('start_date'))
                                                  value="{{ app('request')->input('start_date') }}"
                                              @endif

                                          />
                                          <span class="input-group-addon">
                                             <span class="fa fa-calendar"></span>
                                          </span>
                                       </div>

                                    </td>

                                    <td>
                                      
                                      <div class='input-group date' id='end_date_group'>
                                          <input 
                                              type='text' 
                                              class="form-control" 
                                              placeholder="End Date" 
                                              id='end_date'
                                              name="end_date" 
                                              
                                              @if (app('request')->input('end_date'))
                                                  value="{{ app('request')->input('end_date') }}"
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

                                      <select class="selectpicker form-control" name="limit" 
                                        data-style="form-control btn-default btn-outline">

                                            <li class="mb-10">
                                              
                                              <option value="20"
                                                  @if (app('request')->input('limit') == 20)
                                                      selected="selected"
                                                  @endif
                                                >
                                                20
                                              </option>

                                            </li>

                                            <li class="mb-10">

                                              <option value="50"
                                                  @if (app('request')->input('limit') == 50)
                                                      selected="selected"
                                                  @endif
                                                >
                                                50
                                              </option>

                                            </li>

                                            <li class="mb-10">

                                              <option value="100"
                                                  @if (app('request')->input('limit') == 100)
                                                      selected="selected"
                                                  @endif
                                                >
                                                100
                                              </option>

                                            </li>

                                       </select>
                                      
                                    </td>
                                    
                                    
                                    <td>
                                      <button class="btn btn-primary">Filter</button>
                                    </td>
                                 </tr>
                               </table>
                            </form>
                             
                          </div>
                          <div class="clearfix"></div>

                       </div>


                     @if (!count($ebooks)) 

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
                                              <th width="5%">ID</th>
                                              <th width="20%">Title</th>
                                              <th width="15%">Description</th>
                                              <th width="15%">PDF Link</th>
                                              <th width="15%">Status</th>
                                              <th width="15%">Created At</th>
                                              <th width="15%">Actions</th>
                                           </tr>
                                        </thead>
                                        <tbody>

                                           @foreach ($ebooks as $ebook)                                  
                                             <tr>

                                                <td>
                                                  <span class="txt-dark">
                                                    {{ $ebook->id }}
                                                  </span>
                                                </td>

                                                <td>                                                     
                                                  <span class="txt-dark">
                                                      {{ $ebook->title }} 
                                                  </span>
                                                </td>

                                                <td>
                                                   <span>
                                                      {{ $ebook->description }} 
                                                   </span>
                                                </td>

                                                <td>
                                                  <span class="txt-dark">
                                                    <a href="<?=public_path()?>{{ $ebook->src }}" target="_blank">View PDF</a>
                                                  </span>
                                                </td>

                                                <td>
                                                    
                                                    @if ($ebook->status->id == 1)
                                                      <span class="txt-dark text-success">
                                                          {{ $ebook->status->name }}
                                                      </span>
                                                    @else
                                                      <span class="txt-dark text-danger">
                                                          {{ $ebook->status->name }}
                                                      </span>
                                                    @endif

                                                </td>

                                                <td>
                                                   <span class="txt-dark">
                                                    {{ formatFriendlyDate($ebook->created_at) }}
                                                   </span>
                                                </td>
                                                
                                                <td>

                                                   <a href="{{ route('ebooks.show', $ebook->id) }}" class="btn btn-info btn-sm btn-icon-anim btn-square">
                                                    <i class="zmdi zmdi-eye"></i> 
                                                   </a>

                                                   <a href="{{ route('ebooks.edit', $ebook->id) }}" class="btn btn-primary btn-sm btn-icon-anim btn-square">
                                                    <i class="zmdi zmdi-edit"></i> 
                                                   </a>

                                                   <a href="{{ route('ebooks.destroy', $ebook->id) }}" class="btn btn-danger btn-sm btn-icon-anim btn-square">
                                                    <i class="zmdi zmdi-delete"></i> 
                                                   </a>

                                                </td>
                                             </tr>
                                           @endforeach 
                                           
                                        </tbody>
                                     </table>
                                  </div>
                               </div>
                               <hr>
                               <div class="text-center mb-20">
                                   
                                   {{ $ebooks->links() }}

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

  <script src="{{ asset('admin/js/bootstrap-datetimepicker.min.js') }}"></script>
  <script src="{{ asset('admin/js/bootstrap-select.min.js') }}"></script>

  <!-- search scripts -->
  @include('admin.layouts.searchScripts')
  <!-- /search scripts -->

@endsection
