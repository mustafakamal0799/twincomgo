<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Refresh Token</title>
</head>
<body>
    {{-- <form action="https://account.accurate.id/oauth/authorize" method="POST">
        @csrf
        <input type="text" name="client_id" value="a2c9abc6-8445-4933-a758-022e68aa561f">
        <input type="text" name="response_type" value="code">
        <input type="text" name="redirect_uri" id="" value="http://localhost:8003">
        <input type="text" name="scope" id="" value="item_view customer_category_view item_view warehouse_view item_category_view customer_view sales_order_view sales_invoice_view employee_view sellingprice_adjustment_view branch_view stock_mutation_history_view unit_view sales_invoice_save warehouse_view">

        <button type="submit">Perbarui token</button>
    </form> --}}

    <a href="{{ route('accurate.connect') }}">Perbarui token</a>

</body>
</html>