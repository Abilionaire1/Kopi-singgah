/* globals Chart:false */

(() => {
  'use strict'

  // Graphs
  const ctx = document.getElementById('myChart')
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
      ],
      datasets: [{
        data: [
          15339,
          21345,
          18483,
          24003,
          23489,
          24092,
          12034
        ],
        lineTension: 0,
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        borderWidth: 4,
        pointBackgroundColor: '#007bff'
      }]
    },
    options: {
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          boxPadding: 3
        }
      }
    }
  })
})()
const fileInput = document.getElementById('formFile');
    const btnCancel = document.getElementById('btnCancelFile');

    // Tampilkan tombol cancel saat user memilih file
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            btnCancel.style.display = 'block';
        } else {
            btnCancel.style.display = 'none';
        }
    });

    // Fungsi reset saat tombol cancel diklik
    btnCancel.addEventListener('click', function() {
        fileInput.value = ''; // Kosongkan nilai input
        btnCancel.style.display = 'none'; // Sembunyikan tombol lagi
        
        // Opsional: Trigger event change agar sistem lain tahu file sudah dihapus
        fileInput.dispatchEvent(new Event('change')); 
    });