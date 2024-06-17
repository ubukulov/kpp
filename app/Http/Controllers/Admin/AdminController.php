<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CargoItem;
use App\Models\CargoLog;
use App\Models\CargoStock;
use App\Models\User;
use Illuminate\Http\Request;
use Artisan;
use Auth;
use MARK;

class AdminController extends Controller
{
    public function dashboard()
    {
        /*$arr = [
            ["cargo_tonnage_mark" => "333", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00333CTJT20100", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "333", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00333KTJT20103", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT0330JFEK50185", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "428", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00428LL7D01786", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "428", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00428HL7D01787", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00330KFEL50184", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00330CKEL50069", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655HS5512151", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655ES5512152", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "919", "cargo_tonnage_type_id" => "28", "vincode" => "SEM00919TS9R02397", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655LS5512164", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655ES5512166", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655ES5512149", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655LS5512150", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655JS5512160", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655AS5512163", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "S5512161", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "S5512162", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "С27 ", "cargo_tonnage_type_id" => "29", "vincode" => "TWM08275", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "С27 ", "cargo_tonnage_type_id" => "29", "vincode" => "TWM08276", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "C15", "cargo_tonnage_type_id" => "29", "vincode" => "MCW18377", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "C13", "cargo_tonnage_type_id" => "29", "vincode" => "RRA16953", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "-", "cargo_tonnage_type_id" => "30", "vincode" => "-", "cargo_area_id" => "6"],
            ["cargo_tonnage_mark" => "160K", "cargo_tonnage_type_id" => "28", "vincode" => "SZM20207", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "340", "cargo_tonnage_type_id" => "31", "vincode" => "CAT00340TWFK20026", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "352 08B", "cargo_tonnage_type_id" => "31", "vincode" => "HLW10096", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "352 08B", "cargo_tonnage_type_id" => "2", "vincode" => "HLW10096", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655ES5512152", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655LS5512164", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655ES5512166", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655ES5512149", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655LS5512150", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655JS5512160", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655AS5512163", "cargo_area_id" => "7"],
            ["cargo_tonnage_mark" => "374", "cargo_tonnage_type_id" => "25", "vincode" => "RGM10043", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "336", "cargo_tonnage_type_id" => "25", "vincode" => "GDY21520", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "432", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00432AL7E00858", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "432", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00432PL7E00867", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "M317D2", "cargo_tonnage_type_id" => "35", "vincode" => "CATM317DJCA600914", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "428", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00428CL7D01616", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "428", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00428CL7D01632", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H120GC ", "cargo_tonnage_type_id" => "32", "vincode" => "HHX00717", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H120GC ", "cargo_tonnage_type_id" => "32", "vincode" => "HHX00718", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H130GC", "cargo_tonnage_type_id" => "32", "vincode" => "WHT00353", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H130GC", "cargo_tonnage_type_id" => "32", "vincode" => "WHT00354", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H115GC ", "cargo_tonnage_type_id" => "32", "vincode" => "HHW00471", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H130GC", "cargo_tonnage_type_id" => "32", "vincode" => "WHT00350", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H130GC", "cargo_tonnage_type_id" => "32", "vincode" => "WHT00360", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H130GC", "cargo_tonnage_type_id" => "32", "vincode" => "WHT00381", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H120GC", "cargo_tonnage_type_id" => "32", "vincode" => "HHX00715", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "H120GC", "cargo_tonnage_type_id" => "32", "vincode" => "HHX00716", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "432", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00432AL7E00911", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "428", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00428KL7D01764", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00330LKEL50058", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00330HKEL50059", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "160K", "cargo_tonnage_type_id" => "28", "vincode" => "SZM20198", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "PM620", "cargo_tonnage_type_id" => "33", "vincode" => "CAT00PM6H8RF00363", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "333", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00333CTJT20100", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "428", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00428HL7D01787", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "428", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00428LL7D01786", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "426", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00426LJZ401657", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "426", "cargo_tonnage_type_id" => "26", "vincode" => "CAT00426LJZ401660", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "333", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00333KTJT20103", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT0330JFEK50185", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00330KFEL50184", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "M317D", "cargo_tonnage_type_id" => "35", "vincode" => "CATM317DACA600738", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00330CTEK10691", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "323", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00323EYBL30417", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "822", "cargo_tonnage_type_id" => "34", "vincode" => "SEM00822ES8T00761", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "822", "cargo_tonnage_type_id" => "34", "vincode" => "SEM00822CS8T00759", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "374", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00374JRGM10037", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "352", "cargo_tonnage_type_id" => "25", "vincode" => "CAT00352KHLW00345", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "922", "cargo_tonnage_type_id" => "28", "vincode" => "SEM00922AS9T00819", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655VS5512002", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655AS5512003", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM653", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00653PSL705450", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM653", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00653KSL705451", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655CS5512001", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM518", "cargo_tonnage_type_id" => "36", "vincode" => "SEM00518TS8Y00507", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "AD30", "cargo_tonnage_type_id" => "37", "vincode" => "CAT0AD30TGXR01187", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "-", "cargo_tonnage_type_id" => "30", "vincode" => "-", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "330", "cargo_tonnage_type_id" => "25", "vincode" => "KEL01035", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "С27 ", "cargo_tonnage_type_id" => "29", "vincode" => "TWM08275", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "С27 ", "cargo_tonnage_type_id" => "29", "vincode" => "TWM08276", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "C15", "cargo_tonnage_type_id" => "29", "vincode" => "MCW18377", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "C13", "cargo_tonnage_type_id" => "29", "vincode" => "RRA16953", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655HS5512151", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "919", "cargo_tonnage_type_id" => "28", "vincode" => "SEM00919TS9R02397", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "832", "cargo_tonnage_type_id" => "34", "vincode" => "SEM00832CR8T00163", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655LS5512164", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655ES5512166", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM816D", "cargo_tonnage_type_id" => "34", "vincode" => "SEM00816LS8N03151", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM816D", "cargo_tonnage_type_id" => "34", "vincode" => "SEM00816AS8N03150", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655PS5512074", "cargo_area_id" => "5"],
            ["cargo_tonnage_mark" => "SEM655", "cargo_tonnage_type_id" => "27", "vincode" => "SEM00655KS5512075", "cargo_area_id" => "5"]
        ];*/

        /*foreach ($arr as $item) {
            $cargoItem = CargoItem::create([
                'cargo_id' => 1, 'cargo_area_id' => $item['cargo_area_id'], 'cargo_tonnage_type_id' => $item['cargo_tonnage_type_id'],
                'cargo_tonnage_mark' => $item['cargo_tonnage_mark'], 'vincode' => $item['vincode'], 'status' => 'processing',
                'type' => 'manual'
            ]);

            CargoStock::create([
                'cargo_id' => 1, 'cargo_item_id' => $cargoItem->id, 'status' => 'received'
            ]);

            CargoLog::create([
                'user_id' => 116, 'cargo_id' => 1, 'cargo_item_id' => $cargoItem->id, 'action_type' => 'received'
            ]);
        }*/

        return view('admin.dashboard');
    }

    public function getSendByWhatsApp()
    {
        return view('admin.mail.index');
    }

    public function sendByWhatsApp(Request $request)
    {
        $message = $request->input('prefer');
        Artisan::call('whatsapp:run', [
            'msg' => $message
        ]);

        return redirect()->route('admin.whatsapp.index');
    }

    public function authByEmployee($employee_id)
    {
        $user = User::findOrFail($employee_id);
        Auth::login($user);
        return redirect()->route('cabinet');
    }

    public function mark()
    {
        MARK::getKMForProducts();
    }
}
