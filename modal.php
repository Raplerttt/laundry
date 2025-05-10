<?php
// Contoh variabel untuk modal
$modalType = 'success'; // Bisa: success, error, info, warning
$modalMessage = 'Data berhasil disimpan!';
$modalVisible = true; // true: tampilkan modal
?>

<?php if ($modalVisible): ?>
<div class="fixed z-50 inset-0 overflow-y-auto" id="status-modal">
  <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
      <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
         role="dialog" aria-modal="true" aria-labelledby="modal-headline">
      <div>
        <?php
          $iconColor = [
            'success' => 'text-green-600 bg-green-100',
            'error' => 'text-red-600 bg-red-100',
            'info' => 'text-blue-600 bg-blue-100',
            'warning' => 'text-yellow-600 bg-yellow-100'
          ];

          $iconPath = [
            'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
            'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />',
            'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />',
            'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.293 6.293a1 1 0 011.414 0L12 12.586l6.293-6.293a1 1 0 111.414 1.414L13.414 14l6.293 6.293a1 1 0 01-1.414 1.414L12 15.414l-6.293 6.293a1 1 0 01-1.414-1.414L10.586 14 4.293 7.707a1 1 0 010-1.414z" />'
          ];

          $headingText = [
            'success' => 'Success',
            'error' => 'Error',
            'info' => 'Information',
            'warning' => 'Warning'
          ];
        ?>

        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full <?= $iconColor[$modalType] ?>">
          <svg class="h-6 w-6 <?= explode(' ', $iconColor[$modalType])[0] ?>" xmlns="http://www.w3.org/2000/svg" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <?= $iconPath[$modalType] ?>
          </svg>
        </div>
        <div class="mt-3 text-center sm:mt-5">
          <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
            <?= $headingText[$modalType] ?>
          </h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500"><?= $modalMessage ?></p>
          </div>
        </div>
      </div>
      <div class="mt-5 sm:mt-6">
        <button type="button"
                class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-600 sm:text-sm"
                onclick="document.getElementById('status-modal').classList.add('hidden')">
          OK
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
