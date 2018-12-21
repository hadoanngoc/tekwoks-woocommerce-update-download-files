# tekwoks-woocommerce-update-download-files
Add a button to help update download files in all orders

If you use Woocommerce to sell digital products, there is a case when you need to update downloadable files in a product.

If product P has 1 file X to download, then user A bought P, he/she can access to download file X.
After that, admin uploads new file Y to product P. In this case, all previous orders will not be updated. Users who have bought P cannot download Y.

(There is a work-around solution: admin just change file path of the downloadable files without add new lines)

This piece of code allows admin to update all orders which contain a specific product and re-attach new downloadable files of that product to orders, so users can download them.
