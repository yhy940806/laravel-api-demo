<?php

namespace App\Providers;

use Auth;
use Hash;
use Util;
use Validator;
use App\Helpers\Client;
use App\Models\{Core\Auth\AuthModel, Core\App};
use Illuminate\Support\ServiceProvider;
use App\Services\{Common\AccountingTypeService, Accounting\AccountingTransactionTypeService};

class ValidationServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        //
        Validator::extend("uuid", function ($attribute, $value, $parameters, $validator) {
            return preg_match('/[A-F0-9]{8}\-[A-F0-9]{4}\-4[A-F0-9]{3}\-(8|9|A|B)[A-F0-9]{3‌​}\-[A-F0-9]{12}/', $value);
        });

        Validator::extend("sum", function ($attribute, $value, $parameters, $validator) {
            $total = 0;
            if (!is_array($value))
                return (false);

            foreach ($value as $input) {
                if (isset($input["user_payout"]))
                    $total += $input["user_payout"];
            }

            if ($total == 100)
                return (true);
            else
                return (false);
        });

        Validator::extend("sort", function ($attribute, $value, $parameters, $validator) {
            $strSort = Util::lowerLabel($value);
            $arrSort = config("constant.sort");
            foreach ($arrSort as $sort) {
                if ($sort == $strSort)
                    return (true);
            }

            return (false);
        });

        Validator::extend("emailOrstring", function ($attribute, $value, $parameters, $validator) {
            $emailPattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
            $stringPattern = "/[A-Za-z0-9_-]*$/";
            return (preg_match($emailPattern, $value) | preg_match($stringPattern, $value));
        });

        Validator::extend("file_category", function ($attribute, $value, $parameters, $validator) {
            $arrFileCategory = config("constant.soundblock.file_category");

            foreach ($arrFileCategory as $fileCategory) {
                if (Util::lowerLabel($fileCategory) == Util::lowerLabel($value)) {
                    return (true);
                }
            }
            return (false);
        });

        Validator::extend("common_app", function ($attribute, $value, $parameters, $validator) {
            $objApp = App::where("app_name", strtolower($value))->first();
            if ($objApp) {
                return (true);
            } else {
                return (false);
            }
        });

        Validator::extend("auth_name", function ($attribute, $value, $parameters, $validator) {
            $objAuth = AuthModel::whereRaw("lower(auth_name) = (?)", strtolower($value))->first();
            if ($objAuth) {
                return (true);
            } else {
                return (false);
            }
        });

        Validator::extend("platform", function ($attribute, $value, $parameters, $validator) {
            $strPlatform = strtolower($value);

            foreach (config("constant.platform") as $platform) {
                if ($platform === $strPlatform)
                    return (true);
            }
            return (false);
        });

        Validator::extend("auth", function ($attribute, $value, $parameters, $validator) {
            $arrStr = explode(".", $value);
            if (count($arrStr) !== 2) {
                return (false);
            }

            $newAuthName = ucfirst(strtolower($arrStr[0])) . "." . ucfirst(strtolower($arrStr[1]));

            $objAuthName = AuthModel::where("auth_name", $newAuthName)->first();

            if ($objAuthName) {
                return (true);
            } else {
                return (false);
            }
        });

        Validator::extend("file_action", function ($attribute, $value, $parameters, $validator) {

            $arrFileActions = config("constant.file_action");
            $strFileAction = Util::lowerLabel($value);

            foreach ($arrFileActions as $action) {
                if (Util::lowerLabel($action) == $strFileAction) {
                    return (true);
                }
            }

            return (false);
        });

        Validator::extend("project_type", function ($attribute, $value, $parameters, $validator) {

            $arrProjectTypes = config("constant.soundblock.project_type");
            $strProjecType = Util::lowerLabel($value);

            foreach ($arrProjectTypes as $action) {
                if (Util::lowerLabel($action) === $strProjecType) {
                    return (true);
                }
            }

            return (false);
        });

        Validator::extend("notification_state", function ($attribute, $value, $parameters, $validator) {

            $arrNotiState = config("constant.notification.state");
            $strState = Util::lowerLabel($value);

            foreach ($arrNotiState as $strNotiSate) {
                if (Util::lowerLabel($strNotiSate) == $strState || $strState == "*" || $strState == "all") {
                    return (true);
                }
            }

            return (false);
        });

        Validator::extend("account_type", function ($attribute, $value, $parameters, $validator) {

            $arrAccountTypes = config("constant.soundblock.account_type");

            $strAccountType = Util::lowerLabel($value);

            foreach ($arrAccountTypes as $accountType) {
                if (Util::lowerLabel($accountType) == $strAccountType) {
                    return (true);
                }
            }

            return (false);
        });

        Validator::extend("phone_type", function ($attribute, $value, $parameters, $validator) {

            $arrPhoneTypes = config("constant.soundblock.phone_type");

            $strPhoneType = Util::lowerLabel($value);

            foreach ($arrPhoneTypes as $phoneType) {
                if (Util::lowerLabel($phoneType) == $strPhoneType) {
                    return (true);
                }
            }

            return (false);
        });

        Validator::extend("postal_type", function ($attribute, $value, $parameters, $validator) {

            $arrPostalTypes = config("constant.soundblock.postal_type");

            $strPostalType = Util::lowerLabel($value);

            foreach ($arrPostalTypes as $postalType) {
                if (Util::lowerLabel($postalType) === $strPostalType) {
                    return (true);
                }
            }
            return (false);
        });

        Validator::extend("postal_zipcode", function ($attribute, $value, $parameters, $validator) {

            $pattern1 = "/\b\d{5}-\d{4}$/";
            $pattern2 = "/\b\d{5}/";
            return (preg_match($pattern1, $value) || preg_match($pattern2, $value));
        });

        Validator::extend("must_match_old_password", function ($attribute, $value, $parameters, $validator) {

            return (Hash::check($value, Auth::user()->user_password));
        });

        Validator::extend("deployment_status", function ($attribute, $value, $parameters, $validator) {

            $arrStatus = config("constant.soundblock.deployment_status");

            foreach ($arrStatus as $status) {
                if ($status === Util::ucfLabel($value)) {
                    return (true);
                }
            }
            return (false);
        });

        Validator::extend("accounting_type", function ($attribute, $value, $parameters, $validator) {
            /** @var AccountingTypeService */
            $accountingTypeService = resolve(AccountingTypeService::class);
            return (!is_null($accountingTypeService->findByName($value)));
        });

        Validator::extend("transaction_type", function ($attribute, $value, $parameters, $validator) {
            /** @var AccountingTransactionTypeService */
            $transactionTypeService = resolve(AccountingTransactionTypeService::class);
            return (!is_null($transactionTypeService->findByName($value)));
        });

        Validator::extend("support.flag_status", function ($attribute, $value, $parameters, $validator) {

            $arrFlagStatus = config("constant.support.flag_status");

            foreach ($arrFlagStatus as $flagStatus) {
                if (Util::lowerLabel($flagStatus) == Util::lowerLabel($value)) {
                    return (true);
                }
            }

            return (false);
        });

        Validator::extend("support.category", function ($attribute, $value, $parameters, $validator) {

            $arrCategories = config("constant.support.category");

            foreach ($arrCategories as $category) {
                if (Util::lowerLabel($category) == Util::lowerLabel($value)) {
                    return (true);
                }
            }

            return (false);
        });

        Validator::extend("support.user", function ($attribute, $value, $parameters, $validator) {
            $app = Client::app();

            if (is_null($value) && $app->app_name == "office") {
                return false;
            }

            return true;
        });
    }
}
