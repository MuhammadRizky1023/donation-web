@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Tambah Donasi') }}</div>
                <div class="card-body">
                    <form id="donation-form">
                        <div class="form-group">
                            <label for="name">Nama Donasi</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">Jumlah</label>
                            <input type="number" class="form-control" id="amount" required>
                        </div>
                        <div class="form-group">
                            <label for="image">Gambar</label>
                            <input type="file" class="form-control" id="image" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <img id="preview-image" src="#" alt="Gambar Donasi" style="display: none; max-width: 100%; height: auto;"/>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah Donasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('image').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').style.display = 'block';
                document.getElementById('preview-image').src = e.target.result;
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('preview-image').style.display = 'none';
            document.getElementById('preview-image').src = '#';
        }
    });

    document.getElementById('donation-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const name = document.getElementById('name').value;
        const amount = document.getElementById('amount').value;
        const imageFile = document.getElementById('image').files[0];

        if (imageFile) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageData = e.target.result;

                const newDonationRef = db.push();
                newDonationRef.set({
                    name: name,
                    amount: parseInt(amount),
                    image: imageData,
                    created_at: firebase.database.ServerValue.TIMESTAMP
                })
                .then(() => {
                    alert("Donasi berhasil ditambahkan!");
                    window.location.href = "{{ route('donations') }}";
                })
                .catch((error) => {
                    console.error("Error menambahkan donasi: ", error);
                });
            }
            reader.readAsDataURL(imageFile);
        } else {
            alert("Harap pilih gambar untuk donasi.");
        }
    });
</script>
@endsection
