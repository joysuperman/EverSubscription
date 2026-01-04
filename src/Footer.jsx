import { Link } from "react-router-dom";

export default function Footer() {
    return (
        <footer className="bg-gray-800 text-white py-8">
            <div className="container mx-auto px-4">
                <div className="flex align-center justify-between space-x-6">
                    <div className="text-center text-sm text-gray-400">
                        &copy; {new Date().getFullYear()} EverSubscription. All rights reserved.
                    </div>
                    <div className="text-center text-sm text-gray-400">
                        Made with <span className="text-red-500">❤</span> by <Link to="https://joymojumder.com">JOYSUPERMAN</Link>
                    </div>

                </div>
            </div>
        </footer>
    );
}