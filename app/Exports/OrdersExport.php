<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection,WithHeadings,WithMapping
{
    private $request;
    public function __construct( $request)
    {
        $this->request = $request;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $searchFilter = [
            'Book title' => 'Book_title',
            'Book Publisher' => 'Book_publisher',
            'Book Author' => 'Book_author',
            'Buyer name' => 'buyerName',

        ];
        if( $this->request->column_filter == 'Any'){
            $order = Order::with('book')
                ->where('buyerName','LIKE','%'.$this->request->search_by_word.'%')
                ->where('purchaseDate',$this->request->purchaseDate)
                ->orWhereHas('book', function ($query) {
                    $query->where('Book_title','LIKE','%'.$this->request->search_by_word.'%')
                        ->orWhere('Book_publisher','LIKE','%'.$this->request->search_by_word.'%')
                        ->orWhere('Book_author','LIKE','%'.$this->request->search_by_word.'%');
                })->get();
            return  $order;

        }
        elseif ($searchFilter[$this->request->column_filter] == 'Book_title' ||
            $searchFilter[$this->request->column_filter]== 'Book_publisher' ||
            $searchFilter[$this->request->column_filter] == 'Book_author'
        ){
            $orders = Order::with('book')->where('purchaseDate',$this->request->purchaseDate)
                ->whereHas('book',function ($query) use($searchFilter){
                    $query->where($searchFilter[$this->request->column_filter],$this->request->search_by_word);
                })->get();
            return $orders;
        }
        else {
            $orders = Order::with('book')->where( $searchFilter[$this->request->column_filter],$this->request->search_by_word)
                ->where('purchaseDate',$this->request->purchaseDate)->get();
            return $orders;
        }

    }
    public function map($order): array
    {
        return [
            $order->Book_id,
            $order->book->Book_title,
            $order->book->Book_publisher,
            $order->purchaseDate,
            $order->book->Book_author,
            $order->numberOfUnits,
            $order->totalPrice,
            $order->buyerName,
        ];
    }

    public function headings(): array
    {
        return ["ID","Book title","Publisher","Purchase Date","Author","Purchased Units","Total Cost","Buyer name"];
    }


}
