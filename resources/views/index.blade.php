<html>
<head>
    <title>Invoicify</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="text-center">
<h1 class="h3 mb-3 font-weight-normal">Welcome to Invoicify</h1>
<div class="invoice-form">
    @if ($error)
        <div class="alert alert-danger" role="alert">
            {{ $error }}
        </div>
    @endif
    <form method="POST" action="/invoice" enctype="multipart/form-data">
        @csrf
        <div class="custom-file custom-width">
            <label class="custom-file-label" for="invoice">Upload Invoice</label>
            <input type="file" class="custom-file-input" id="invoice" name="invoice" required accept=".csv">
        </div>
        <div class="form-group custom-width">
            <label for="currecies">Currencies</label>
            <input type="text" class="form-control" id="currecies" name="currecies" required>
        </div>
        <div class="form-group custom-width">
            <label for="outputCurrency">Output Currency</label>
            <input type="text" class="form-control" id="outputCurrency" name="outputCurrency" required>
        </div>
        <div class="form-group custom-width">
            <label for="vat">VAT</label>
            <input type="text" class="form-control" id="vat" name="vat">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</body>
</html>