<?php
/* @var $filter ModelFilter */

$pageCount = $filter->getPageCount();
$activePage = $filter->getActivePage();
$middleLinkNumber = 2;

//Вычисления значений
$startPage = $activePage - $middleLinkNumber;
$startSpacer = true;
if ($startPage <= 1)
{
    $startPage = 1;
    $startSpacer = false;
}
$endPage = $activePage + $middleLinkNumber;
$endSpacer = true;
if ($endPage >= $pageCount)
{
    $endPage = $pageCount;
    $endSpacer = false;
}
?>

<?php if ($filter->getPageCount() > 1): ?>
    <div class="pagination">
            
            <?php if ($startSpacer): ?>
         <div class="item spacer">
                <a href="<?= $filter->firstPage() ?>">1</a>
         </div>
                <div class="item">
                    <a>...</a>
                </div>
            <?php endif; ?>
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="item <?= $filter->ifPageActive($i, "active") ?>">
                    <a href="<?= $filter->page($i) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($endSpacer): ?>
                <li class="item spacer">
                    ...
                </li>
                <div class="item">
                <a href="<?= $filter->lastPage() ?>"><?=$filter->getPageCount()?></a>
            </div>
            <?php endif; ?>
            
    </div>

<?php endif; ?>