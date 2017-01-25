<?php if (!class_exists('CFRuntime')) die('No direct access allowed.');
/**
 * Stores your AWS account information. Add your account information, and then rename this file
 * to 'config.inc.php'.
 *
 * @version 2011.01.20
 * @license See the included NOTICE.md file for more information.
 * @copyright See the included NOTICE.md file for more information.
 * @link http://aws.amazon.com/php/ PHP Developer Center
 * @link http://aws.amazon.com/security-credentials AWS Security Credentials
 */


/**
 * Amazon Web Services Key. Found in the AWS Security Credentials. You can also pass this value as the first
 * parameter to a service constructor.
 */
define('AWS_KEY', 'AKIAIOVMINKBMKFBIE2A');

/**
 * Amazon Web Services Secret Key. Found in the AWS Security Credentials. You can also pass this value as
 * the second parameter to a service constructor.
 */
define('AWS_SECRET_KEY', 'cF57qaeqlE43OusO4tWXLIGv6NlSvB0Dk2rEwp2e');

/**
 * Amazon Account ID without dashes. Used for identification with Amazon EC2. Found in the AWS Security
 * Credentials.
 */
define('AWS_ACCOUNT_ID', '7317-6103-6908');

/**
 * Your CanonicalUser ID. Used for setting access control settings in AmazonS3. Found in the AWS Security
 * Credentials.
 */
define('AWS_CANONICAL_ID', '7ed17c979c83910aaac2fe90fcd6c49d7743c8ac2c496c6381fee57565a43448');

/**
 * Your CanonicalUser DisplayName. Used for setting access control settings in AmazonS3. Found in the AWS
 * Security Credentials (i.e. "Welcome, AWS_CANONICAL_NAME").
 */
define('AWS_CANONICAL_NAME', 'vtONE Admin');

/**
 * 12-digit serial number taken from the Gemalto device used for Multi-Factor Authentication. Ignore this
 * if you're not using MFA.
 */
define('AWS_MFA_SERIAL', '');

/**
 * Amazon CloudFront key-pair to use for signing private URLs. Found in the AWS Security Credentials. This
 * can be set programmatically with <AmazonCloudFront::set_keypair_id()>.
 */
define('AWS_CLOUDFRONT_KEYPAIR_ID', 'APKAIBIYGH2ZZ435NOLA');

/**
 * The contents of the *.pem private key that matches with the CloudFront key-pair ID. Found in the AWS
 * Security Credentials. This can be set programmatically with <AmazonCloudFront::set_private_key()>.
 */
define('AWS_CLOUDFRONT_PRIVATE_KEY_PEM', '-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAgQ1FNupCZ4LJd7alxdYgeUOJjD44cjZmZsK0FZuY0DniCSsO
bFPUOXyuMQ7eL34KTVgl6rxoyd52jv2/JKjJUsrrHsaU/WEHT+WRH54gq6vXhfD+
Bd8F6oeeEyeCet11mfxtDC2+TkjGodX1tyLvZbJ8z07EtUeRPfyn4hoLs/IWHXs9
uj+Ck6k+C8wwP3mIWoaXpb7r0LGe/yD1mgH+GRiT5xAo8aOPjFdn7L06J6sbImIq
FwHuRE/FBcrXGbns0O7rNrd+F4dvMsxe3cCdJ7zNAhjH5LHAZFNcmyCiVHlXPPJ0
BRrXx86Az4jlKIKJJKweoZzwP2cDW1RKYfWFnwIDAQABAoIBAEbJ7baSx3waHMMj
GEmuDEAYUOHx22qi9obVtIzJvggySA/5Yz7+uMIT50UXv77TZ3lHqfzZ/q0E74m+
HNRSFaTplBFcoqteRvGHnpR2W7tvVpitOdoknQ0p+QbOvF8DDZg7A+ITUXmFqBdr
0w3zBtiFELtynKpHqJ8U8U4wNU0t6d+RagJD7eSLODPw84GYXLMcBUMCVhSl5WOc
JIZeQKdx3AJ5786S+LPJABa6ekyD8L1ZeI2TV45WsVwTpSd1lvmU92f1NVSoqgaK
1kTIi3CdY6jFswIdNLfoyVXkx+fLlKhfjR03PIjns6oERmDqOGo/XvX7+mh3X0cA
FiQuImkCgYEA3F+609MYQb0im5KFN1QZ3N27vMXR154k32sKwg8cNfEFTxFG4sK0
MuLzmAC+njQXBn0r3+4csNFBPEXxxis+cIN+lq6oVzNKQ3cVmCWsL5f7Zj/s0283
qwBUQkrqM1e550Vo3t45N8D/w3uoFrs9ylQE9pRQMI6lTeTQY6+tEqUCgYEAleoi
0UMnDYFIy8S31xUtFCpuO+hwVD7iRhDafTcBGxIhlh00gqGuM3XMhfEPklomhrtm
dUrivVO8aFQGmsTNxmleWWj4hMkXzRAEBXAr10R6fmDbou/Npusq4dZeOm3B/Zuh
NuPll+hMddUzFsizUi2FpIGyPMHFUjR8IYQAF/MCgYEAnlBjXhNTZL6kIxEyhJn1
bncYjLesVXL12E8Ezn6ebJ32i2PFAdiQLdJe3v8B8ZNIS1AW+esMT3Y0oEE7PHsK
gzfj9AoLQ4HEQw1ExSWjOhm78CvSTd6jJkS5Q1qgPzwxgFSbzyfkAQq0ctHd4l6n
ODf9zMqlhQyk8n2Du2mUM0UCgYBi3f/KTGQz9uBgakLn2PJay0TZw4hZNwOZO8Is
NBtJlCKMUoRv5lrxWy3f48PmPAgOcQa4MgPo4pFtqISWi1Y+FP2BL8Y+JDTLK1XL
lFeFZ4b1U8Fl6oqRG6SzPeH03K/EJmAiyBeBoFTUnR9NVl1Uw+rQPCyk/xG4Dh8T
J2+8WwKBgQDMdoRrAo0/fD2cY91TNc3nlE2p2TvgfK9I4n7QMaj52V9iOPgIa8fV
9GaZZgWZMeq2E3rHyjBnDeMXFp3fD9C4kIw5fwUya1h4q0rkv5hR7qwyCZgDvGQy
p6GBle8YANbiN+fcIwTP4m3GdrQKqXSwEH9fVehuZR13TXC74fdpwA==
-----END RSA PRIVATE KEY-----');

/**
 * Set the value to true to enable autoloading for classes not prefixed with "Amazon" or "CF". If enabled,
 * load `sdk.class.php` last to avoid clobbering any other autoloaders.
 */
define('AWS_ENABLE_EXTENSIONS', 'false');
