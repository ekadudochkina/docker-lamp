<?php
/* @var $filter ModelFilter */

$pageCount = $filter->getPageCount();
$activePage = $filter->getActivePage();
$middleLinkNumber = 4;

//Вычисления значений
$startPage = $activePage - $middleLinkNumber;
$startSpacer = true;
if ($startPage < 1)
{
    $startPage = 1;
    $startSpacer = false;
}
$endPage = $activePage + $middleLinkNumber;
$endSpacer = true;
if ($endPage > $pageCount)
{
    $endPage = $pageCount;
    $endSpacer = false;
}
?>

<?php if ($filter->getPageCount() > 1): ?>
    <nav class="text-center">
        <ul class="pagination pagination-lg">
            <li>
                <a href="<?= $filter->firstPage() ?>">«</a>
            </li>
            <?php if ($startSpacer): ?>
                <li>
                    <a>...</a>
                </li>
            <?php endif; ?>
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="<?= $filter->ifPageActive($i, "active") ?>">
                    <a href="<?= $filter->page($i) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($endSpacer): ?>
                <li>
                    <a>...</a>
                </li>
            <?php endif; ?>
            <li>
                <a href="<?= $filter->lastPage() ?>">»</a>
            </li>
        </ul>
    </nav>

<?php endif; ?>