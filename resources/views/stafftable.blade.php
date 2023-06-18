<div class="col-md-12" id="staffs">
          @if(count($staffs)>0)
          <table class="table table-w-80">
            <thead class="border-n">
              <tr>
                <th>Name</th>
                <th>Email ID</th>
              </tr>
            </thead>
            <tbody class="tr-border td-styles tr-hover">
              @foreach($staffs as $staff)
              <tr>
                <td><b>{{ucfirst($staff->name)}}</b></td>
                <td>{{$staff->email}}</td>
                <td><img src="img/dots.png" id="dropdownMenuButton" data-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                  <div class="dropdown-menu">
                    <a href="javascript:void(0)" data-id='{{$staff->id}}' class="edit dropdown-item">Edit</a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @else
          <p>No staff found here.</p>
          @endif
        </div>