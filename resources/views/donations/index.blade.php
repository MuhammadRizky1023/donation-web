@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Daftar Donasi') }}</div>
                <div class="card-body">
                    <a href="{{ route('donations.create') }}" class="btn btn-primary mb-3">Tambah Donasi</a>
                    <div id="donation-list" class="row"></div>
                    <p id="no-donations-message" class="text-center" >No donations available</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    db.collection("donations").orderBy("created_at", "desc").get().then((querySnapshot) => {
        const donationList = document.getElementById('donation-list');
        const noDonationsMessage = document.getElementById('no-donations-message');

        if (querySnapshot.empty) {
            noDonationsMessage.style.display = 'block';
        } else {
            querySnapshot.forEach((doc) => {
                const donation = doc.data();
                const card = document.createElement('div');
                card.className = 'col-md-4';
                card.innerHTML = `
                    <div class="card mb-4">
                        <img src="${donation.image}" class="card-img-top" alt="Donation Image">
                        <div class="card-body">
                            <h5 class="card-title">${donation.name}</h5>
                            <p class="card-text">Rp${donation.amount.toLocaleString()}</p>
                            <a href="{{ url('donations/${doc.id}/edit') }}" class="btn btn-primary">Update</a>
                            <button class="btn btn-danger" onclick="deleteDonation('${doc.id}')">Delete</button>
                        </div>
                    </div>
                `;
                donationList.appendChild(card);
            });
        }
    }).catch((error) => {
        console.error("Error getting donations: ", error);
    });

    function deleteDonation(id) {
        if (confirm("Apakah Anda yakin ingin menghapus donasi ini?")) {
            db.collection("donations").doc(id).delete().then(() => {
                alert("Donasi berhasil dihapus!");
                location.reload();
            }).catch((error) => {
                console.error("Error removing document: ", error);
            });
        }
    }
</script>
@endsection
