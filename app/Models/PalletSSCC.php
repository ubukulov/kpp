<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class PalletSSCC extends Model
{
    use HasFactory;
    protected $table = 'pallet_sscc';
    protected $fillable = [
        'user_id', 'task_id', 'code', 'status', 'type', 'standard', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateSSCC($marking_id, $flag = 1, $standard)
    {
        // (01) 1 487025215 0000001 k
        // (02) 2 487025215 0000001 k
        if($flag == 1) {
            $lastItem = PalletSSCC::where('type', 'box')->orderBy('id', 'DESC')->first();
        } else {
            $lastItem = PalletSSCC::where('type', 'pallet')->orderBy('id', 'DESC')->first();
        }

        $sscc = ($flag == 1) ? "1".env('GLN') : "2".env('GLN');
        $type = ($flag == 1) ? 'box' : 'pallet';
        if($lastItem) {
            $number = substr($lastItem->code, 15, 7);
            $number = $number + 1;
            switch (strlen($number)) {
                case 1:
                    $number = "000000" . $number;
                    break;
                case 2:
                    $number = "00000" . $number;
                    break;
                case 3:
                    $number = "0000" . $number;
                    break;
                case 4:
                    $number = "000" . $number;
                    break;
                case 5:
                    $number = "00" . $number;
                    break;
                case 6:
                    $number = "0" . $number;
                    break;
            }
            $sscc .= $number;
            $sscc .= self::checkControlNumber($sscc);
        } else {
            $sscc .= "0000001";
            $sscc .= self::checkControlNumber($sscc);
        }

        $sscc = ($flag == 1) ? "(01) ".$sscc : "(02) ".$sscc;
        $pallet_sscc = PalletSSCC::create([
            'user_id' => Auth::id(), 'code' => $sscc, 'task_id' => $marking_id, 'status' => 'waiting',
            'type' => $type, 'standard' => $standard
        ]);

        return $pallet_sscc->code;
    }

    public static function checkControlNumber($number)
    {
        //$number = "14870252150000001";
        $arr = str_split((string) $number);
        $arr = array_reverse($arr);
        $new_arr = [];
        foreach($arr as $k=>$v) {
            $i = $k+1;
            $new_arr[$i] = $arr[$k];
        }
        $sum1 = 0; // нечётное
        $sum2 = 0; // чётное
        foreach ($new_arr as $k=>$v) {
            if($k % 2 == 0) {
                $sum2 += $new_arr[$k];
            } else {
                $sum1 += $new_arr[$k];
            }
        }

        $sum1 = $sum1 * 3;

        $sum = $sum1 + $sum2;

        return ($sum % 10 == 0) ? 0 : 10 - $sum % 10;
    }
}
