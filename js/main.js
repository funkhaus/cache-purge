$j = jQuery.noConflict();

window.cp = {
    // This cp_vars object is injected from WordPress via wp_add_inline_script
    apiUrl: cp_vars.apiUrl,
    nonce: cp_vars.nonce,
    loading: false,
    status: null,
    init() {
        $j("#wpadminbar").on(
            "click",
            ".cp-purge-button:not(.cp-status-no-configured-providers) > a",
            function (e) {
                e.preventDefault();
                cp.runPurge();
            }
        );
    },
    async runPurge() {
        if (cp.loading) {
            return;
        }

        cp.loading = true;
        cp.setPurgeStatus("loading");

        try {
            await fetch(cp.apiUrl + "/purge", {
                method: "POST",
                mode: "same-origin",
                cache: "no-cache",
                credentials: "include",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": cp.nonce,
                },
            }).then((res) => {
                if (res.ok) {
                    return res;
                }

                throw new Error(`${res.status}: ${res.statusText}`);
            });

            cp.setPurgeStatus("success");
        } catch (e) {
            alert("There was an error when trying to purge caches.");
            cp.setPurgeStatus("error");
            console.log("error", e);
        } finally {
            setTimeout(() => {
                cp.setPurgeStatus();
                cp.loading = false;
            }, 2000);
        }
    },
    setPurgeStatus(status) {
        cp.status = status;

        $j(".cp-purge-button").removeClass(
            "cp-status-success cp-status-loading cp-status-error"
        );

        if (cp.status) {
            $j(".cp-purge-button").addClass("cp-status-" + status);
        }
    },
};

jQuery(document).ready(function () {
    cp.init();
});
