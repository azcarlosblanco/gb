<?php namespace Modules\Payment\Entities;

interface PolicyPaymentDetail {

	const S_WAIT_CONFIRMATION = 0;
    const S_CONFIRM = 1;
    const S_CANCELLED = 2;

	public function getProdceudreDocument();

}