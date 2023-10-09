<div class="col-md-12" id="staffs">
          @if(count($staffs)>0 || count($foremans)>0)
          <table class="table table-w-80">
            <thead class="border-n">
              <tr>
              <th>Name</th>
                <th>Email ID</th>
                <th>User type</th>
                <th>Leaves</th>
              </tr>
            </thead>
            <tbody class="tr-border td-styles tr-hover">
                @foreach($foremans as $foreman)
              <tr>
                <td><b>{{ucfirst($foreman->name)}}</b></td>
                <td>{{$foreman->email}}</td>
                <td>Foreman</td>
                <td><button class="btn btn-color btn-primary">Manage</button></td>
              </tr>
              @endforeach
              @foreach($staffs as $staff)
              <tr>
                <td><b>{{ucfirst($staff->name)}}</b></td>
                <td>{{$staff->email}}</td>
                <td>Staff</td>
                <td><button class="btn btn-color btn-primary">Manage</button></td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @else
          <p>No user found here.</p>
          @endif
        </div>