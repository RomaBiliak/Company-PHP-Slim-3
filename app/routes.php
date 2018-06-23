<?php

$app->get('/', 'CompanyController:index');
$app->get('/company-data/{company_id}', 'CompanyController:companyData');
$app->get('/{company_id}', 'CompanyController:getCompanyById');
$app->put('/{company_id}', 'CompanyController:updateCompany');
$app->delete('/{company_id}', 'CompanyController:deleteCompany');
$app->post('/', 'CompanyController:addCompany');
