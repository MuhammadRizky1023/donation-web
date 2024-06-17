@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Donasi') }}</div>
                <div class="card-body">
                    <form id="edit-donation-form">
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
                            <input type="file" class="form-control-file" id="image">
                            <img src="" id="image-preview" class="mt-2" style="max-width: 200px;">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Donasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const donationId = "{{ $id }}";
    const dbRef = firebase.database().ref('donations').child(donationId);

    dbRef.once('value', (snapshot) => {
        const donation = snapshot.val();
        if (donation) {
            document.getElementById('name').value = donation.name;
            document.getElementById('amount').value = donation.amount;

            // Menampilkan gambar jika ada
            if (donation.imageURL) {
                document.getElementById('image-preview').src = donation.imageURL;
            }
        } else {
            console.log("Donasi tidak ditemukan!");
        }
    }).catch((error) => {
        console.error("Error getting donation:", error);
    });

    document.getElementById('edit-donation-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const name = document.getElementById('name').value;
        const amount = document.getElementById('amount').value;
        const imageFile = document.getElementById('image').files[0];

        if (imageFile) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const imageURL = event.target.result;
                document.getElementById('image-preview').src = imageURL;
                updateDonationData(name, parseInt(amount), imageURL);
            };
            reader.readAsDataURL(imageFile);
        } else {
            updateDonationData(name, parseInt(amount), null);
        }
    });

    function updateDonationData(name, amount, imageURL) {
        const donationData = {
            name: name,
            amount: amount,
            updated_at: firebase.database.ServerValue.TIMESTAMP
        };

        if (imageURL) {
            donationData.imageURL = imageURL;
        }

        dbRef.update(donationData)
        .then(() => {
            alert("Donasi berhasil diupdate!");
            window.location.href = "{{ route('donations') }}";
        })
        .catch((error) => {
            console.error("Error updating donation: ", error);
        });
    }
</script>
@endsection
