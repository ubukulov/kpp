<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContainerAddress;
use Illuminate\Http\Request;
use Str;

class ContainerAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $title = "СПРЕДЕР-КОНСОЛЬ";
        $kind = 'k';
        $zone = strtoupper(substr(Str::slug($title),0,2).$kind);



        /*$row = 25;
        $place = 8;
        $floor = 4;
        $space = "DAMU";

        for ($i=13; $i<=$row; $i++) {
            for($j=1; $j<=$place; $j++) {
                for($k=1; $k<=$floor; $k++) {
                    $data = [
                        'title' => $title,
                        'zone' => $zone,
                        'kind' => 'r',
                        'row'  => $i,
                        'place' => $j,
                        'floor' => $k,
                        'name' => $zone."-".$i."-".$j."-".$k,
                        'space' => $space
                    ];
                    ContainerAddress::create($data);
                }
            }
        }
*/
        /*
        $row = 25;
        $space = "DAMU";

        for ($i=1; $i<=$row; $i++) {
            $s1 = $zone."-".$i."-1-1";
            $s2 = $zone."-".$i."-1-2";
            $s3 = $zone."-".$i."-2-1";
            $s4 = $zone."-".$i."-2-2";
            $s5 = $zone."-".$i."-2-3";
            $data = [
                'title' => $title,
                'zone' => $zone,
                'kind' => 'k',
                'row'  => $i,
                'space' => $space
            ];

            $data['place'] = 1;
            $data['floor'] = 1;
            $data['name'] = $s1;
            ContainerAddress::create($data);

            $data['place'] = 1;
            $data['floor'] = 2;
            $data['name'] = $s2;
            ContainerAddress::create($data);

            $data['place'] = 2;
            $data['floor'] = 1;
            $data['name'] = $s3;
            ContainerAddress::create($data);

            $data['place'] = 2;
            $data['floor'] = 2;
            $data['name'] = $s4;
            ContainerAddress::create($data);

            $data['place'] = 2;
            $data['floor'] = 3;
            $data['name'] = $s5;
            ContainerAddress::create($data);
        }*/

        $container_address = ContainerAddress::all();
        return view('admin.container_address.index', compact('container_address'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.container_address.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['title'] = mb_strtoupper($data['title']);
        $data['zone'] = strtoupper(substr(Str::slug($data['title']), 0, 2)).strtoupper($data['kind']);

        $data['name'] = $data['zone']."-".$data['row']."-".$data['place']."-".$data['floor'];
        ContainerAddress::create($data);
        return redirect()->route('admin.container-address.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $container_address = ContainerAddress::find($id);
        return view('admin.container_address.edit', compact('container_address'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
