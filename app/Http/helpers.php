<?php

use App\Models\Option;


/**
 * Just pass the option name to get the option values.
 * Value can be array or string.
 * @param $option_name
 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|string|null
 */
function GetOption($option_name)
{
    $data_return = (object)array();
    $data_return->id = -1;
    $data_return->option_value = "";
    try {
        $data_return = Option::query()
            ->where('option_name', "=", $option_name)
            ->first();
        if (!is_null($data_return)) {
            $data_return->option_value = $data_return->is_array = 1 ? json_decode($data_return->option_value) : $data_return->option_value;
        }
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return $data_return;
}


/**
 * Save option option can be array type or string type.
 * There is no need to encode json.
 * @param $option_name
 * @param $option_value
 * @return mixed
 */
function SaveOption($option_name, $option_value)
{
    $option_data = new Option();
    $option_data->option_name = $option_name;
    $option_data->is_array = is_array($option_value) ? 1 : 0;
    $option_data->option_value = is_array($option_value) ? json_encode($option_value) : $option_value;
    $option_data->save();
    return $option_data->id;
}

/**
 * Update option by id based on option name and option value
 * if option value is array type it store json encoded data
 * @param $id
 * @param $option_name
 * @param $option_value
 * @return mixed
 */
function UpdateOption($id, $option_name, $option_value)
{
//    $option_data = new Options();
    $option_data = Option::find($id); // new Options();
    $option_data->option_name = $option_name;
    $option_data->is_array = is_array($option_value) ? 1 : 0;
    $option_data->option_value = is_array($option_value) ? json_encode($option_value) : $option_value;
    $option_data->save();
    return $option_data;
}
/*
Autor :: Umar Farooq 
Description :: Function For Conversion Number Value Into Words
Date :: 2/16/2022
*/
function AmountInWords(float $amount)
{
   $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
   // Check if there is any number after decimal
   $amt_hundred = null;
   $count_length = strlen($num);
   $x = 0;
   $string = array();
   $change_words = array(0 => '', 1 => 'One', 2 => 'Two',
     3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
     7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
     10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
     13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
     16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
     19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
     40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
     70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $here_digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $x < $count_length ) {
      $get_divider = ($x == 2) ? 10 : 100;
      $amount = floor($num % $get_divider);
      $num = floor($num / $get_divider);
      $x += $get_divider == 10 ? 1 : 2;
      if ($amount) {
       $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
       $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
       $string [] = ($amount < 21) ? $change_words[$amount].' '. $here_digits[$counter]. $add_plural.' 
       '.$amt_hundred:$change_words[floor($amount / 10) * 10].' '.$change_words[$amount % 10]. ' 
       '.$here_digits[$counter].$add_plural.' '.$amt_hundred;
        }
   else $string[] = null;
   }
   $implode_to_Rupees = implode('', array_reverse($string));
   $get_paise = ($amount_after_decimal > 0) ? "And " . ($change_words[$amount_after_decimal / 10] . " 
   " . $change_words[$amount_after_decimal % 10]) . ' Paise' : '';
   return ($implode_to_Rupees ? $implode_to_Rupees . ' ' : '') . $get_paise;
}